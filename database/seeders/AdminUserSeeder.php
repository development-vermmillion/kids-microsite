<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Creates the first admin account (ADMIN_EMAIL / ADMIN_PASSWORD in .env).
 * Without ADMIN_PASSWORD: "password" on a local copy, a random password on the
 * live site (printed once). Change it under Admin users after the first login.
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('kidsavon.admin.email');

        if (User::where('email', $email)->exists()) {
            return;
        }

        $password = config('kidsavon.admin.password')
            ?: (app()->isProduction() ? Str::password(14, symbols: false) : 'password');

        User::create(['email' => $email, 'name' => 'Admin', 'password' => $password]);

        $this->command?->info("Admin account created: {$email} / {$password}");
    }
}
