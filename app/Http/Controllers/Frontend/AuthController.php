<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Rider;
use App\Support\CurrentRider;
use App\Support\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Rider registration and login with an OTP sent to their email.
 *
 * Register: name, username, email, mobile -> a code is emailed -> the account is
 * created with the email marked as verified.
 * Login: email -> a code is emailed -> logged in.
 * Both forms (and "Send OTP") also pass the bot traps and the Cloudflare robot check.
 */
class AuthController extends Controller
{
    public const MOBILE_RULE = ['required', 'regex:/^\+?[0-9]{10,13}$/'];

    public const MOBILE_MESSAGE = 'Please enter a valid 10-digit mobile number.';

    public const EMAIL_RULE = ['required', 'string', 'email:rfc', 'max:120'];

    public function __construct(private OtpService $otp, private CurrentRider $current) {}

    public function showLogin(Request $request): View|RedirectResponse
    {
        if ($this->current->check()) {
            return redirect()->route('home');
        }

        $this->rememberRedirect($request);

        return view('frontend.pages.login', ['mode' => 'login']);
    }

    public function showJoin(Request $request): View|RedirectResponse
    {
        if ($this->current->check()) {
            return redirect()->route('home');
        }

        $this->rememberRedirect($request);

        return view('frontend.pages.login', ['mode' => 'join', 'prefillEmail' => $request->query('email')]);
    }

    /** "Send OTP" (called by JavaScript; also works as a normal form post). */
    public function sendOtp(Request $request): JsonResponse|RedirectResponse
    {
        $request->merge(['email' => OtpService::normaliseEmail($request->input('email'))]);
        $purpose = $request->input('purpose') === 'register' ? 'register' : 'login';

        $request->validate(['email' => self::EMAIL_RULE], [], ['email' => 'email address']);
        $email = $request->input('email');

        if ($purpose === 'register' && Rider::where('email', $email)->exists()) {
            throw ValidationException::withMessages(['email' => 'This email already has a rider account. Please log in instead.']);
        }

        // Spot a taken username before the email goes out, not after.
        $username = mb_strtolower(trim((string) $request->input('username')));
        if ($purpose === 'register' && $username !== '' && Rider::where('username', $username)->exists()) {
            throw ValidationException::withMessages(['username' => 'This username is taken. Please try another one.']);
        }

        $name = null;
        if ($purpose === 'login') {
            $rider = $this->riderForLogin($email);
            $name = $rider->name;
        } else {
            $name = $request->input('name');
        }

        if ($error = $this->otp->send($email, $purpose, $name)) {
            throw ValidationException::withMessages(['email' => $error]);
        }

        $message = OtpService::testCode()
            ? 'Code ready! (Testing mode: use '.OtpService::testCode().')'
            : "We emailed a 6-digit code to {$email}. It works for ".OtpService::expiresMinutes().' minutes. Not in your inbox? Check Spam or Promotions.';

        return $request->expectsJson()
            ? response()->json(['message' => $message, 'resend_seconds' => (int) config('kidsavon.otp.resend_seconds', 60)])
            : back()->withInput()->with('otp_sent', $message);
    }

    public function login(Request $request): RedirectResponse
    {
        $request->merge(['email' => OtpService::normaliseEmail($request->input('email'))]);

        $data = $request->validate([
            'email' => self::EMAIL_RULE,
            'otp' => ['required', 'string', 'max:10'],
        ], ['otp.required' => 'Please enter the code we emailed you.'], ['email' => 'email address', 'otp' => 'OTP']);

        $rider = $this->riderForLogin($data['email']);
        $this->checkOtp($data['email'], 'login', $data['otp']);

        // Receiving the code proves the rider owns this email.
        if (! $rider->email_verified_at) {
            $rider->forceFill(['email_verified_at' => now()])->save();
        }

        $this->current->login($rider);

        return redirect()->intended(route('home'))->with('success', "Welcome back, {$rider->name}! Ready to ride?");
    }

    public function join(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => OtpService::normaliseEmail($request->input('email')),
            'username' => mb_strtolower(trim((string) $request->input('username'))),
            'mobile' => preg_replace('/[^0-9+]/', '', (string) $request->input('mobile')),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'regex:'.Rider::USERNAME_REGEX, Rule::unique('riders', 'username')],
            'email' => [...self::EMAIL_RULE, Rule::unique('riders', 'email')],
            'mobile' => self::MOBILE_RULE,
            'otp' => ['required', 'string', 'max:10'],
        ], [
            'username.regex' => 'Use 3–20 letters, numbers, dots or underscores (no spaces).',
            'username.unique' => 'This username is taken. Please try another one.',
            'email.unique' => 'This email already has a rider account. Please log in instead.',
            'mobile.regex' => self::MOBILE_MESSAGE,
            'otp.required' => 'Please enter the code we emailed you.',
        ], ['name' => 'rider name', 'email' => 'email address', 'mobile' => 'mobile number', 'otp' => 'OTP']);

        $this->checkOtp($data['email'], 'register', $data['otp']);

        $rider = Rider::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'email_verified_at' => now(),
            'mobile' => $data['mobile'],
        ]);
        $this->current->login($rider);

        return redirect()->intended(route('home'))
            ->with('success', "Welcome to Kids Avon, {$rider->name}! Upload your first ride to start earning badges.");
    }

    public function logout(): RedirectResponse
    {
        $this->current->logout();

        return redirect()->route('login')->with('info', 'You have logged out. See you on your next ride!');
    }

    /** The active rider with this email, or a friendly error. */
    private function riderForLogin(string $email): Rider
    {
        $rider = Rider::where('email', $email)->first();

        if (! $rider) {
            throw ValidationException::withMessages([
                'email' => 'We could not find a rider with this email. New here? Tap “Join the Adventure”.',
            ]);
        }

        if (! $rider->is_active) {
            throw ValidationException::withMessages(['email' => 'This account is paused. Please contact us for help.']);
        }

        return $rider;
    }

    /** ?redirect=/challenges sends the rider back to that page after logging in (same-site paths only). */
    private function rememberRedirect(Request $request): void
    {
        $to = (string) $request->query('redirect');

        if (str_starts_with($to, '/') && ! str_starts_with($to, '//')) {
            $request->session()->put('url.intended', url($to));
        }
    }

    private function checkOtp(string $email, string $purpose, string $code): void
    {
        if ($error = $this->otp->check($email, $purpose, $code)) {
            throw ValidationException::withMessages(['otp' => $error]);
        }
    }
}
