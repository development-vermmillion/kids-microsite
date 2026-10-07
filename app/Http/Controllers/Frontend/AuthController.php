<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Rider;
use App\Support\CurrentRider;
use App\Support\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/** Rider login and sign-up with mobile number + OTP. */
class AuthController extends Controller
{
    public const MOBILE_RULE = ['required', 'regex:/^\+?[0-9]{10,13}$/'];

    public const MOBILE_MESSAGE = 'Please enter a valid 10-digit mobile number.';

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

        return view('frontend.pages.login', ['mode' => 'join', 'prefillMobile' => $request->query('mobile')]);
    }

    /** "Send OTP" (called by JavaScript; also works as a normal form post). */
    public function sendOtp(Request $request): JsonResponse|RedirectResponse
    {
        $request->merge(['mobile' => OtpService::normalise($request->input('mobile'))]);
        $request->validate(['mobile' => self::MOBILE_RULE], ['mobile.regex' => self::MOBILE_MESSAGE]);

        $this->otp->send($request->input('mobile'));

        $message = OtpService::testCode()
            ? 'OTP sent! (Testing: use '.OtpService::testCode().')'
            : 'OTP sent to '.$request->input('mobile').'. It is valid for '.OtpService::EXPIRES_MINUTES.' minutes.';

        return $request->expectsJson()
            ? response()->json(['message' => $message])
            : back()->withInput()->with('otp_sent', $message);
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $this->validateMobileAndOtp($request);

        $rider = Rider::where('mobile', $data['mobile'])->first();

        if (! $rider) {
            throw ValidationException::withMessages([
                'mobile' => 'We could not find a rider with this number. New here? Tap “Join the Adventure”.',
            ]);
        }

        if (! $rider->is_active) {
            throw ValidationException::withMessages(['mobile' => 'This account is paused. Please contact us for help.']);
        }

        $this->checkOtp($data['mobile'], $data['otp']);
        $this->current->login($rider);

        return redirect()->intended(route('home'))->with('success', "Welcome back, {$rider->name}! Ready to ride?");
    }

    public function join(Request $request): RedirectResponse
    {
        $request->merge(['mobile' => OtpService::normalise($request->input('mobile'))]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'mobile' => [...self::MOBILE_RULE, 'unique:riders,mobile'],
            'otp' => ['required', 'string', 'max:10'],
        ], [
            'mobile.regex' => self::MOBILE_MESSAGE,
            'mobile.unique' => 'This number already has an account. Please log in instead.',
        ], ['name' => 'rider name', 'mobile' => 'mobile number', 'otp' => 'OTP']);

        $this->checkOtp($data['mobile'], $data['otp']);

        $rider = Rider::create(['name' => $data['name'], 'mobile' => $data['mobile']]);
        $this->current->login($rider);

        return redirect()->intended(route('home'))
            ->with('success', "Welcome to Kids Avon, {$rider->name}! Upload your first ride to start earning badges.");
    }

    public function logout(): RedirectResponse
    {
        $this->current->logout();

        return redirect()->route('login')->with('info', 'You have logged out. See you on your next ride!');
    }

    /** ?redirect=/challenges sends the rider back to that page after logging in (same-site paths only). */
    private function rememberRedirect(Request $request): void
    {
        $to = (string) $request->query('redirect');

        if (str_starts_with($to, '/') && ! str_starts_with($to, '//')) {
            $request->session()->put('url.intended', url($to));
        }
    }

    private function validateMobileAndOtp(Request $request): array
    {
        $request->merge(['mobile' => OtpService::normalise($request->input('mobile'))]);

        return $request->validate([
            'mobile' => self::MOBILE_RULE,
            'otp' => ['required', 'string', 'max:10'],
        ], ['mobile.regex' => self::MOBILE_MESSAGE], ['mobile' => 'mobile number', 'otp' => 'OTP']);
    }

    private function checkOtp(string $mobile, string $code): void
    {
        if ($error = $this->otp->check($mobile, $code)) {
            throw ValidationException::withMessages(['otp' => $error]);
        }
    }
}
