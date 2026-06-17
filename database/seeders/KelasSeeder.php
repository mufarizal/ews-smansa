<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KelasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kelasData = [
            ['nama_kelas'=>'10 A'],
            ['nama_kelas'=>'10 B'],
            ['nama_kelas'=>'10 C'],
            ['nama_kelas'=>'11 D'],
            ['nama_kelas'=>'11 E'],
            ['nama_kelas'=>'11 F'],
            ['nama_kelas'=>'12 G'],
            ['nama_kelas'=>'12 H'],
        ];

        foreach ($kelasData as $data) {
            \App\Models\Kelas::create($data);
        }
    }
}
