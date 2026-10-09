<?php

namespace App\Support;

use App\Models\Rider;
use Illuminate\Support\Facades\Session;

/**
 * The rider who is logged in on the website (email + OTP).
 * Guests get null.
 */
class CurrentRider
{
    private ?Rider $rider = null;

    private bool $resolved = false;

    public function get(): ?Rider
    {
        if (! $this->resolved) {
            $this->resolved = true;

            $id = Session::get('rider_id');
            $rider = $id ? Rider::find($id) : null;

            // A rider paused by the admin is logged out.
            if ($rider && ! $rider->is_active) {
                Session::forget('rider_id');
                $rider = null;
            }

            $this->rider = $rider;
        }

        return $this->rider;
    }

    public function check(): bool
    {
        return $this->get() !== null;
    }

    public function login(Rider $rider): void
    {
        Session::regenerate();
        Session::put('rider_id', $rider->id);
        $rider->forceFill(['last_login_at' => now()])->save();

        $this->rider = $rider;
        $this->resolved = true;
    }

    public function logout(): void
    {
        Session::forget('rider_id');
        Session::regenerateToken();

        $this->rider = null;
        $this->resolved = true;
    }
}
