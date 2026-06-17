<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run()
    {
        $roles = [
            ['name' => 'Admin', 'slug' => 'admin'],
            ['name' => 'Guru BK', 'slug' => 'guru_bk'],
            ['name' => 'Guru Mapel', 'slug' => 'guru_mapel'],
            ['name' => 'Wali Kelas', 'slug' => 'wali_kelas'],
            ['name' => 'Guru Piket', 'slug' => 'guru_piket'],
            ['name' => 'Kurikulum', 'slug' => 'kurikulum'],
            ['name' => 'Siswa', 'slug' => 'siswa'],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
