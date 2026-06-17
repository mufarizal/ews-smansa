<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create all roles
        $this->call(RoleSeeder::class);

        // Create users for each role
        $this->call(AdminSeeder::class);
        $this->call(KelasSeeder::class);
        $this->call(JadwalSeeder::class);
    }
}
