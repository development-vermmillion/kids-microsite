<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Support\CurrentRider;
use App\Support\Uploads;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Rider's own settings: name, mobile number and profile photo. */
class SettingsController extends Controller
{
    public function edit(CurrentRider $current): View
    {
        $rider = $current->get();
        abort_unless($rider, 404);

        return view('frontend.pages.settings', [
            'rider' => $rider,
            'verifiedKm' => (float) $rider->rides()->verified()->sum('distance_km'),
            'badgesEarned' => $rider->badges()->wherePivotNotNull('unlocked_at')->count(),
        ]);
    }

    public function update(Request $request, CurrentRider $current): RedirectResponse
    {
        $rider = $current->get();
        abort_unless($rider, 404);

        $request->merge(['mobile' => preg_replace('/[^0-9+]/', '', (string) $request->input('mobile'))]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'mobile' => AuthController::MOBILE_RULE,
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'avatar.max' => 'The photo is too big. Please use one under 2 MB.',
            'mobile.regex' => AuthController::MOBILE_MESSAGE,
        ], ['mobile' => 'mobile number']);

        if ($request->hasFile('avatar') || $request->boolean('remove_avatar')) {
            Uploads::delete($rider->avatar);
            $data['avatar'] = $request->hasFile('avatar') ? Uploads::store($request->file('avatar'), 'avatars') : null;
        }

        $rider->update($data);

        return redirect()->route('settings.edit')->with('success', 'Your profile is saved!');
    }
}
