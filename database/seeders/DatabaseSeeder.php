<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        $adminRole = Role::where('slug', 'admin')->first();

        $user = User::create([
            'name' => 'Admin System',
            'email' => 'admin@system.com',
            'password' => Hash::make('password'),
        ]);

        $user->roles()->attach($adminRole->id);
    }
}
