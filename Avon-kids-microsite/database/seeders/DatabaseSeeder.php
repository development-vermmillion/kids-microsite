<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the database with the placeholder content from the original HTML design.
     */
    public function run(): void
    {
        $this->call([
            KidsAvonDemoSeeder::class,
        ]);
    }
}
