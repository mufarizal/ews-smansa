<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PenugasanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $semesterId = 1;

        // Mapping nama kelas => id
        $kelas = [
            'A' => 3,
            'B' => 4,
            'C' => 5,
            'D' => 6,
            'E' => 7,
            'F' => 8,
            'G' => 9,
            'H' => 10,
            'I' => 11,
            'J' => 12,
        ];

        // Mapping nama mapel => id
        $mapel = [
            'PPKn' => 1,
            'Matematika' => 2,
            'Fisika' => 11,
            'Ekonomi' => 13,
            'Geografi' => 15,
            'Sejarah' => 8,
            'PJOK' => 16,
            'Bahasa Inggris' => 17,
            'Bahasa Indonesia' => 19,
            'Bahasa Sunda' => 20,
            'PABP' => 21,
            'Seni Budaya' => 10,
            'Biologi' => 22,
            'Sosiologi' => 6,
            'Kimia' => 4,
            'Prakarya dan Kewirausahaan' => 24,
            'Bahasa Jepang' => 26,
            'Informatika' => 25,
        ];

        // Mapping nama guru => id
        $guru = [
            'Drs. Masduki, M.Pd' => 17,
            'Mutia Maisaroh, S.Pd' => 18,
            'Suryani, S.Pd' => 5,
            'Suharto, S.Pd' => 20,
            'Hj. Euis Gardini, S.Pd' => 21,
            'Siti Nuraini, S.Pd' => 22,
            'Lia Muliawati, S.Pd' => 11,
            'Neneng Mariah, M.Pd' => 23,
            'Kholijah, S.Pd' => 37,
            'Hudri, S.Ag' => 4,
            'Irfan Noormansyah, S.Pd' => 24,
            'Wardiana, S.Pd, M.Si' => 19,
            'Popy Arba Noor Rahmat, S.Pd' => 9,
            'Inggit Luhur Ningtias, S.Pd' => 12,
            'Nurliana Sidabutar, S.P.' => 6,
            'Sri Sukmawati, S.H.,Gr' => 29,
            'Evi Widianti, S.Pd' => 30,
            'Ismaya Rahmat, S.Pd' => 31,
            'Asep Sukandar, A.Md' => 32,
            'Dian Nurdiansyah, S.Pd' => 33,
            'Mukhamad Ikhsan Alwy, S.Pd' => 27,
            'Euis Siti Nurhayati, S.Pd' => 10,
            'Khalda Naura Danianto, S.Pd' => 34,
            'Galih Permadi Pratama, S.Kom' => 35,
            'Nuraeni U, S.Pd.I' => 36,
            'Nur Aisyah Awaliyah Rahmah, S.Pd' => 8,
            'Freddy Setiawan, S.Pd' => 13,
            'Drs. Ajat Sudrajat' => 25,
        ];

        // ============================================================
        // Data penugasan: [guru_key, mapel_key, [kelas huruf...]]
        // ============================================================
        $assignments = [
            ['Drs. Masduki, M.Pd', 'PPKn', ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J']],
            ['Mutia Maisaroh, S.Pd', 'Matematika', ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H']],
            ['Suryani, S.Pd', 'Fisika', ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J']],
            ['Suharto, S.Pd', 'Ekonomi', ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J']],
            ['Hj. Euis Gardini, S.Pd', 'Geografi', ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J']],
            ['Siti Nuraini, S.Pd', 'Sejarah', ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J']],
            ['Lia Muliawati, S.Pd', 'PJOK', ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H']],
            ['Neneng Mariah, M.Pd', 'Bahasa Inggris', ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J']],
            ['Kholijah, S.Pd', 'Bahasa Indonesia', ['A', 'B', 'C', 'D', 'E', 'F', 'G']],
            ['Kholijah, S.Pd', 'Bahasa Sunda', ['A', 'B', 'C', 'D']],
            ['Hudri, S.Ag', 'PABP', ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J']],
            ['Irfan Noormansyah, S.Pd', 'PJOK', ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H']],  // H ditambah sesuai data (sama dgn Lia)
            ['Wardiana, S.Pd, M.Si', 'Seni Budaya', ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J']],
            ['Popy Arba Noor Rahmat, S.Pd', 'Bahasa Indonesia', ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H']],
            ['Inggit Luhur Ningtias, S.Pd', 'Seni Budaya', ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J']],
            ['Nurliana Sidabutar, S.P.', 'Biologi', ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J']],
            ['Sri Sukmawati, S.H.,Gr', 'PPKn', ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J']],
            ['Sri Sukmawati, S.H.,Gr', 'Sosiologi', ['A', 'B', 'C', 'D', 'E', 'F']],
            ['Evi Widianti, S.Pd', 'Kimia', ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H']],
            ['Evi Widianti, S.Pd', 'Prakarya dan Kewirausahaan', ['A', 'B', 'C', 'D']],
            ['Ismaya Rahmat, S.Pd', 'PPKn', ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I']],
            ['Ismaya Rahmat, S.Pd', 'Sosiologi', ['A', 'B', 'C', 'D', 'E', 'F']],
            ['Asep Sukandar, A.Md', 'Bahasa Jepang', ['A', 'B', 'C', 'D', 'E', 'F', 'G']],
            ['Dian Nurdiansyah, S.Pd', 'Bahasa Indonesia', ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H']],
            ['Dian Nurdiansyah, S.Pd', 'Bahasa Sunda', ['A', 'B', 'C', 'D', 'E', 'F']],
            ['Mukhamad Ikhsan Alwy, S.Pd', 'Matematika', ['A', 'B']],
            ['Euis Siti Nurhayati, S.Pd', 'Matematika', ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J']],
            ['Khalda Naura Danianto, S.Pd', 'Matematika', ['A']],
            ['Galih Permadi Pratama, S.Kom', 'Informatika', ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J']],
            ['Nuraeni U, S.Pd.I', 'PABP', ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J']],
            ['Nur Aisyah Awaliyah Rahmah, S.Pd', 'Bahasa Inggris', ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J']],
            ['Freddy Setiawan, S.Pd', 'PJOK', ['A', 'B', 'C', 'D', 'E', 'F', 'G']],
            ['Drs. Ajat Sudrajat', 'PJOK', ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H']],
        ];

        $rows = [];
        $now = now();

        foreach ($assignments as [$guruKey, $mapelKey, $kelasList]) {
            $guruId = $guru[$guruKey] ?? null;
            $mapelId = $mapel[$mapelKey] ?? null;

            if (!$guruId || !$mapelId) {
                $this->command->warn("SKIP — guru/mapel tidak ditemukan: {$guruKey} / {$mapelKey}");
                continue;
            }

            foreach ($kelasList as $huruf) {
                $kelasId = $kelas[$huruf] ?? null;
                if (!$kelasId) {
                    $this->command->warn("SKIP — kelas tidak ditemukan: {$huruf}");
                    continue;
                }

                $rows[] = [
                    'guru_id' => $guruId,
                    'semester_id' => $semesterId,
                    'mapel_id' => $mapelId,
                    'kelas_id' => $kelasId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // Insert dalam chunk agar tidak overload
        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('guru_mapel_kelas')->insert($chunk);
        }

        $this->command->info('GuruMapelKelasSeeder selesai — ' . count($rows) . ' baris diinsert.');
    }
}
