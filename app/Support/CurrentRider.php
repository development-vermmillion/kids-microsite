<?php

namespace App\Support;

use App\Models\Rider;

/**
 * Resolves the rider the frontend is shown for.
 *
 * Mobile + OTP login is not built yet, so this falls back to the demo rider
 * (config/kidsavon.php). Once login exists, store the rider id in the session
 * under "rider_id" and every page picks it up automatically.
 */
class CurrentRider
{
    private ?Rider $rider = null;

    private bool $resolved = false;

    public function get(): ?Rider
    {
        if (! $this->resolved) {
            $this->resolved = true;

            $id = session('rider_id');

            $this->rider = $id
                ? Rider::find($id)
                : Rider::where('mobile', config('kidsavon.demo_rider_mobile'))->first();
        }

        return $this->rider;
    }
}
