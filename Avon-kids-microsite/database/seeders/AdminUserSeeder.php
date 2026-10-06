<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Creates the first admin account.
 * Set ADMIN_EMAIL / ADMIN_PASSWORD in .env before seeding, or change the
 * password from the admin panel (Admins) after the first login.
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@kidsavon.com')],
            ['name' => 'Kids Avon Admin', 'password' => env('ADMIN_PASSWORD', 'password')],
        );
    }
}
