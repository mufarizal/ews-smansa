<?php

namespace Database\Seeders;

use App\Models\Mapel;
use Illuminate\Database\Seeder;

class MapelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mapelData = [
            ['nama' => 'PPKn'],
            ['nama' => 'Matematika'],
            ['nama' => 'Matematika Tingkat Lanjut'],
            ['nama' => 'Kimia'],
            ['nama' => 'Kimia Tingkat Lanjut'],
            ['nama' => 'Sosiologi'],
            ['nama' => 'Sosiologi Tingkat Lanjut'],
            ['nama' => 'Sejarah'],
            ['nama' => 'Sejarah Tingkat Lanjut'],
            ['nama' => 'Seni Budaya'],
            ['nama' => 'Fisika'],
            ['nama' => 'Fisika Tingkat Lanjut'],
            ['nama' => 'Ekonomi'],
            ['nama' => 'Ekonomi Tingkat Lanjut'],
            ['nama' => 'Geografi'],
            ['nama' => 'PJOK'],
            ['nama' => 'Bahasa Inggris'],
            ['nama' => 'Bahasa Inggris Tingkat Lanjut'],
            ['nama' => 'Bahasa Indonesia'],
            ['nama' => 'Bahasa Sunda'],
            ['nama' => 'PABP'],
            ['nama' => 'Biologi'],
            ['nama' => 'Biologi Tingkat Lanjut'],
            ['nama' => 'Prakarya dan Kewirausahaan'],
            ['nama' => 'Informatika'],
            ['nama' => 'Bahasa Jepang'],
        ];

        foreach ($mapelData as $data) {
            Mapel::create($data);
        }
    }
}
