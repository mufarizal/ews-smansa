<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JadwalSeeder extends Seeder
{
    /**
     * ATURAN JADWAL (revisi):
     * - Sekolah masuk 06:30, pulang 14:00
     * - 1 JP = 45 menit
     * - Istirahat 1: 09:30–10:00
     * - Istirahat 2: 12:15–13:15
     *   (catatan: 12:00–13:00 tidak bisa pas kalau istirahat 1 tetap di
     *   09:30–10:00 dan semua JP 45 menit — 120 menit di antara keduanya
     *   tidak habis dibagi 45. Digeser 15 menit ke 12:15 supaya total
     *   tetap 8 JP/hari dan pulang persis jam 14:00.)
     * - PJOK selalu di jam pertama, 3 JP (135 menit), 1x seminggu
     *   (untuk kelas yang punya PJOK, dikunci di hari Selasa)
     * - Setiap hari maksimal 4–5 mapel per kelas (blok @2 JP)
     * - Jumat: 45 menit kegiatan (rotasi 4 minggu: Senam Bersama,
     *   Jumat Bersih, Literasi, Kewalikelasan), lalu 5 JP mapel
     * - Idempotent: data lama semester ybs dihapus dulu sebelum insert baru,
     *   TIDAK perlu migrate:fresh untuk generate ulang jadwal
     */

    // ── Penugasan guru per kelas per mapel (guru_id terkecil) ──────────
    private function getPenugasan(): array
    {
        $raw = [
            [1, 3, 17],
            [1, 4, 17],
            [1, 5, 17],
            [1, 6, 17],
            [1, 7, 17],
            [1, 8, 17],
            [1, 9, 17],
            [1, 10, 17],
            [1, 11, 17],
            [1, 12, 17],

            [2, 3, 10],
            [2, 4, 10],
            [2, 5, 10],
            [2, 6, 10],
            [2, 7, 10],
            [2, 8, 10],
            [2, 9, 10],
            [2, 10, 10],
            [2, 11, 10],
            [2, 12, 10],

            [4, 3, 30],
            [4, 4, 30],
            [4, 5, 30],
            [4, 6, 30],
            [4, 7, 30],
            [4, 8, 30],
            [4, 9, 30],
            [4, 10, 30],

            [6, 3, 29],
            [6, 4, 29],
            [6, 5, 29],
            [6, 6, 29],
            [6, 7, 29],
            [6, 8, 29],
            [6, 9, 31],
            [6, 10, 31],
            [6, 11, 31],
            [6, 12, 31],

            [8, 3, 22],
            [8, 4, 22],
            [8, 5, 22],
            [8, 6, 22],
            [8, 7, 22],
            [8, 8, 22],
            [8, 9, 22],
            [8, 10, 22],
            [8, 11, 22],
            [8, 12, 22],

            [10, 3, 12],
            [10, 4, 12],
            [10, 5, 12],
            [10, 6, 12],
            [10, 7, 12],
            [10, 8, 12],
            [10, 9, 12],
            [10, 10, 12],
            [10, 11, 12],
            [10, 12, 12],

            [11, 3, 5],
            [11, 4, 5],
            [11, 5, 5],
            [11, 6, 5],
            [11, 7, 5],
            [11, 8, 5],
            [11, 9, 5],
            [11, 10, 5],
            [11, 11, 5],
            [11, 12, 5],

            [13, 3, 20],
            [13, 4, 20],
            [13, 5, 20],
            [13, 6, 20],
            [13, 7, 20],
            [13, 8, 20],
            [13, 9, 20],
            [13, 10, 20],
            [13, 11, 20],
            [13, 12, 20],

            [15, 3, 21],
            [15, 4, 21],
            [15, 5, 21],
            [15, 6, 21],
            [15, 7, 21],
            [15, 8, 21],
            [15, 9, 21],
            [15, 10, 21],
            [15, 11, 21],
            [15, 12, 21],

            [16, 3, 11],
            [16, 4, 11],
            [16, 5, 11],
            [16, 6, 11],
            [16, 7, 11],
            [16, 8, 11],
            [16, 9, 11],
            [16, 10, 11],

            [17, 3, 8],
            [17, 4, 8],
            [17, 5, 8],
            [17, 6, 8],
            [17, 7, 8],
            [17, 8, 8],
            [17, 9, 8],
            [17, 10, 8],
            [17, 11, 8],
            [17, 12, 8],

            [19, 3, 9],
            [19, 4, 9],
            [19, 5, 9],
            [19, 6, 9],
            [19, 7, 9],
            [19, 8, 9],
            [19, 9, 9],
            [19, 10, 9],

            [20, 3, 33],
            [20, 4, 33],
            [20, 5, 33],
            [20, 6, 33],
            [20, 7, 33],
            [20, 8, 33],

            [21, 3, 4],
            [21, 4, 4],
            [21, 5, 4],
            [21, 6, 4],
            [21, 7, 4],
            [21, 8, 4],
            [21, 9, 4],
            [21, 10, 4],
            [21, 11, 4],
            [21, 12, 4],

            [22, 3, 6],
            [22, 4, 6],
            [22, 5, 6],
            [22, 6, 6],
            [22, 7, 6],
            [22, 8, 6],
            [22, 9, 6],
            [22, 10, 6],
            [22, 11, 6],
            [22, 12, 6],

            [24, 3, 30],
            [24, 4, 30],
            [24, 5, 30],
            [24, 6, 30],

            [25, 3, 35],
            [25, 4, 35],
            [25, 5, 35],
            [25, 6, 35],
            [25, 7, 35],
            [25, 8, 35],
            [25, 9, 35],
            [25, 10, 35],
            [25, 11, 35],
            [25, 12, 35],

            [26, 3, 32],
            [26, 4, 32],
            [26, 5, 32],
            [26, 6, 32],
            [26, 7, 32],
            [26, 8, 32],
            [26, 9, 32],
        ];

        $map = [];
        foreach ($raw as [$mapelId, $kelasId, $guruId]) {
            $map[$kelasId][$mapelId] = $guruId;
        }

        return $map;
    }

    // ── Urutan prioritas mapel (yang di depan dapat jatah JP ekstra duluan) ──
    // Mapel "inti" ditaruh di depan supaya kalau ada sisa JP, mereka yang
    // dapat tambahan dulu (MTK, B.Indo, B.Inggris dst).
    private const PRIORITAS_MAPEL = [
        2,  // Matematika
        19, // Bahasa Indonesia
        17, // Bahasa Inggris
        21, // PABP
        11, // Fisika
        22, // Biologi
        1,  // PPKn
        8,  // Sejarah
        13, // Ekonomi
        15, // Geografi
        6,  // Sosiologi
        4,  // Kimia
        10, // Seni Budaya
        25, // Informatika
        20, // Bahasa Sunda
        24, // Prakarya
        26, // Bahasa Jepang
    ];

    private const NAMA_MAPEL = [
        1 => 'PPKn',
        2 => 'Matematika',
        4 => 'Kimia',
        6 => 'Sosiologi',
        8 => 'Sejarah',
        10 => 'Seni Budaya',
        11 => 'Fisika',
        13 => 'Ekonomi',
        15 => 'Geografi',
        16 => 'PJOK',
        17 => 'Bahasa Inggris',
        19 => 'Bahasa Indonesia',
        20 => 'Bahasa Sunda',
        21 => 'PABP',
        22 => 'Biologi',
        24 => 'Prakarya',
        25 => 'Informatika',
        26 => 'Bahasa Jepang',
    ];

    private const PJOK_ID = 16;

    private const HARI_PJOK = 'Selasa'; // hari PJOK dikunci di jam pertama

    // ── Slot waktu Senin/Selasa/Rabu/Kamis (45 menit/JP) ────────────────
    private function getSlotHarian(): array
    {
        return [
            ['06:30', '07:15'], // JP1
            ['07:15', '08:00'], // JP2
            ['08:00', '08:45'], // JP3
            ['08:45', '09:30'], // JP4
            // istirahat 09:30–10:00
            ['10:00', '10:45'], // JP5
            ['10:45', '11:30'], // JP6
            ['11:30', '12:15'], // JP7
            // istirahat 12:15–13:15
            ['13:15', '14:00'], // JP8
        ];
    }

    // ── Slot waktu Jumat (kegiatan 06:30–07:15, lalu 5 JP) ──────────────
    private function getSlotJumat(): array
    {
        return [
            ['07:15', '08:00'], // JP1
            ['08:00', '08:45'], // JP2
            ['08:45', '09:30'], // JP3
            // istirahat 09:30–10:00
            ['10:00', '10:45'], // JP4
            ['10:45', '11:30'], // JP5
        ];
    }

    /**
     * Susun kapasitas slot kosong (non-PJOK) per hari untuk 1 kelas.
     * Return: [hari => jumlah_slot_kosong]
     */
    private function getKapasitasHari(bool $adaPjok): array
    {
        $slotSelasa = 8 - ($adaPjok ? 3 : 0); // PJOK ambil 3 slot pertama Selasa

        return [
            'Senin' => 8,
            'Selasa' => $slotSelasa,
            'Rabu' => 8,
            'Kamis' => 8,
            'Jumat' => 5,
        ];
    }

    /**
     * Hitung target JP/minggu tiap mapel non-PJOK untuk 1 kelas, supaya
     * totalnya PAS SAMA DENGAN jumlah slot kosong yang tersedia.
     * Basis 2 JP/mapel, sisa slot dibagi round-robin sesuai prioritas.
     */
    private function hitungTargetJp(array $mapelIds, int $totalSlotTersedia): array
    {
        // Urutkan sesuai prioritas
        $urut = array_values(array_intersect(self::PRIORITAS_MAPEL, $mapelIds));

        $target = [];
        foreach ($urut as $id) {
            $target[$id] = 2; // basis 2 JP/minggu tiap mapel
        }

        $sisa = $totalSlotTersedia - (2 * count($urut));

        // Kalau sisa positif, tambahkan +1 JP bergilir mulai dari mapel prioritas atas
        // Kalau sisa negatif (jarang terjadi), kurangi -1 JP bergilir dari mapel prioritas bawah
        $i = 0;
        $n = count($urut);
        if ($n === 0) {
            return $target;
        }

        while ($sisa > 0) {
            $id = $urut[$i % $n];
            $target[$id]++;
            $sisa--;
            $i++;
        }
        while ($sisa < 0) {
            $id = $urut[($n - 1 - ($i % $n))];
            if ($target[$id] > 1) {
                $target[$id]--;
                $sisa++;
            }
            $i++;
        }

        return $target;
    }

    /**
     * Pecah target JP tiap mapel jadi potongan blok (maks 3 JP/blok,
     * diutamakan 2 JP/blok) supaya gampang ditaruh ke slot harian.
     * Return: array of [mapel_id, jumlah_jp_blok]
     */
    private function pecahJadiBlok(array $target): array
    {
        $blok = [];
        foreach ($target as $mapelId => $jp) {
            while ($jp > 0) {
                $ambil = min(2, $jp); // blok 2 JP, sisa 1 JP jadi blok tunggal
                $blok[] = [$mapelId, $ambil];
                $jp -= $ambil;
            }
        }

        return $blok;
    }

    /**
     * Tempatkan daftar blok mapel ke hari-hari yang tersedia, round-robin
     * antar hari supaya tiap hari dapat 4–5 mapel & tidak dobel mapel yang
     * sama di hari yang sama (kalau memungkinkan).
     * Return: [hari => [ [mapel_id, jp], ... ]]
     */
    private function tempatkanBlok(array $blok, array $kapasitas): array
    {
        $hariUrutan = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $isiHari = array_fill_keys($hariUrutan, []);
        $sisaKapasitas = $kapasitas;

        // Urutkan blok terbesar dulu supaya blok 2 JP tidak ketinggalan
        // di slot yang cuma sisa 1
        usort($blok, fn ($a, $b) => $b[1] <=> $a[1]);

        $ptr = 0;
        foreach ($blok as [$mapelId, $jp]) {
            $ditempatkan = false;

            // coba cari hari yang belum punya mapel ini & masih cukup kapasitas,
            // mulai dari hari giliran (round robin) supaya merata
            for ($offset = 0; $offset < count($hariUrutan); $offset++) {
                $hari = $hariUrutan[($ptr + $offset) % count($hariUrutan)];
                $sudahAdaMapelIni = collect($isiHari[$hari])->pluck(0)->contains($mapelId);

                if (! $sudahAdaMapelIni && $sisaKapasitas[$hari] >= $jp) {
                    $isiHari[$hari][] = [$mapelId, $jp];
                    $sisaKapasitas[$hari] -= $jp;
                    $ptr = ($ptr + $offset + 1) % count($hariUrutan);
                    $ditempatkan = true;
                    break;
                }
            }

            // fallback: kalau semua hari sudah punya mapel ini, tempel di hari
            // manapun yang masih cukup kapasitas (boleh dobel di hari yang sama)
            if (! $ditempatkan) {
                foreach ($hariUrutan as $hari) {
                    if ($sisaKapasitas[$hari] >= $jp) {
                        $isiHari[$hari][] = [$mapelId, $jp];
                        $sisaKapasitas[$hari] -= $jp;
                        $ditempatkan = true;
                        break;
                    }
                }
            }
        }

        return $isiHari;
    }

    /**
     * Ubah isi hari (mapel + jp) menjadi baris jadwal dengan jam_mulai/jam_selesai
     * berdasarkan slot yang tersedia hari itu (memperhitungkan slot yang
     * sudah dipakai PJOK kalau ada).
     */
    private function konversiKeBarisJadwal(
        array $isiHari,
        bool $adaPjok,
        int $kelasId,
        array $penugasanKelas,
        int $semesterId,
        Carbon $now
    ): array {
        $rows = [];

        foreach ($isiHari as $hari => $daftarMapel) {
            if ($hari === 'Jumat') {
                $slot = $this->getSlotJumat();
            } else {
                $slot = $this->getSlotHarian();
                // kalau hari ini hari PJOK, 3 slot pertama sudah dipakai PJOK
                if ($adaPjok && $hari === self::HARI_PJOK) {
                    $slot = array_slice($slot, 3);
                }
            }

            $slotIdx = 0;
            foreach ($daftarMapel as [$mapelId, $jp]) {
                if (! isset($penugasanKelas[$mapelId])) {
                    continue; // jaga-jaga kalau tidak ada guru ditugaskan
                }
                if ($slotIdx + $jp > count($slot)) {
                    continue; // kapasitas tidak cukup, skip (seharusnya tidak terjadi)
                }

                $jamMulai = $slot[$slotIdx][0];
                $jamSelesai = $slot[$slotIdx + $jp - 1][1];

                $rows[] = [
                    'semester_id' => $semesterId,
                    'kelas_id' => $kelasId,
                    'mapel_id' => $mapelId,
                    'guru_id' => $penugasanKelas[$mapelId],
                    'hari' => $hari,
                    'jam_mulai' => $jamMulai,
                    'jam_selesai' => $jamSelesai,
                    'is_active' => true,
                    'catatan' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $slotIdx += $jp;
            }

            // Tambahkan PJOK di jam pertama hari PJOK
            if ($adaPjok && $hari === self::HARI_PJOK && isset($penugasanKelas[self::PJOK_ID])) {
                $slotAsli = $this->getSlotHarian();
                $rows[] = [
                    'semester_id' => $semesterId,
                    'kelas_id' => $kelasId,
                    'mapel_id' => self::PJOK_ID,
                    'guru_id' => $penugasanKelas[self::PJOK_ID],
                    'hari' => $hari,
                    'jam_mulai' => $slotAsli[0][0],   // 06:30
                    'jam_selesai' => $slotAsli[2][1], // 08:45 (3 JP)
                    'is_active' => true,
                    'catatan' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        return $rows;
    }

    public function run(): void
    {
        $semesterId = 1;
        $now = Carbon::now();
        $penugasan = $this->getPenugasan();

        // ── 0. Hapus data lama semester ini supaya bisa generate ulang ──
        // tanpa perlu migrate:fresh
        DB::table('jadwals')->where('semester_id', $semesterId)->delete();
        DB::table('jadwal_kegiatans')->where('semester_id', $semesterId)->delete();

        $kelasList = [3, 4, 5, 6, 7, 8, 9, 10, 11, 12]; // 10A–10J
        $jadwals = [];

        foreach ($kelasList as $kelasId) {
            $penugasanKelas = $penugasan[$kelasId] ?? [];
            $adaPjok = isset($penugasanKelas[self::PJOK_ID]);

            $mapelNonPjok = array_keys($penugasanKelas);
            $mapelNonPjok = array_values(array_diff($mapelNonPjok, [self::PJOK_ID]));

            $kapasitas = $this->getKapasitasHari($adaPjok);
            $totalSlotTersedia = array_sum($kapasitas);

            $target = $this->hitungTargetJp($mapelNonPjok, $totalSlotTersedia);
            $blok = $this->pecahJadiBlok($target);
            $isiHari = $this->tempatkanBlok($blok, $kapasitas);

            $rows = $this->konversiKeBarisJadwal(
                $isiHari,
                $adaPjok,
                $kelasId,
                $penugasanKelas,
                $semesterId,
                $now
            );

            $jadwals = array_merge($jadwals, $rows);
        }

        foreach (array_chunk($jadwals, 100) as $chunk) {
            DB::table('jadwals')->insert($chunk);
        }

        $this->command->info('✅ Jadwal pelajaran berhasil di-generate: '.count($jadwals).' baris');

        // ── Jadwal kegiatan Jumat (rotasi 4 minggu) ─────────────────────
        $kegiatanList = [
            1 => 'Senam Bersama',
            2 => 'Jumat Bersih',
            3 => 'Literasi',
            4 => 'Kewalikelasan',
        ];

        $kegiatans = [];
        foreach ($kegiatanList as $mingguKe => $namaKegiatan) {
            $kegiatans[] = [
                'semester_id' => $semesterId,
                'hari' => 'Jumat',
                'minggu_ke' => $mingguKe,
                'nama_kegiatan' => $namaKegiatan,
                'jam_mulai' => '06:30',
                'jam_selesai' => '07:15',
                'is_active' => true,
                'catatan' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('jadwal_kegiatans')->insert($kegiatans);

        $this->command->info('✅ Jadwal kegiatan Jumat berhasil di-generate: '.count($kegiatans).' baris');
        $this->command->info('');
        $this->command->info('📋 Ringkasan jadwal per kelas:');

        $byKelas = collect($jadwals)->groupBy('kelas_id');
        $kelasNama = [3 => '10A', 4 => '10B', 5 => '10C', 6 => '10D', 7 => '10E', 8 => '10F', 9 => '10G', 10 => '10H', 11 => '10I', 12 => '10J'];
        foreach ($byKelas as $kId => $rows) {
            $mapelCount = collect($rows)->pluck('mapel_id')->unique()->count();
            $byHari = collect($rows)->groupBy('hari')->map->count();
            $detail = $byHari->map(fn ($c, $h) => "$h:$c")->implode(', ');
            $this->command->line("   Kelas {$kelasNama[$kId]}: {$rows->count()} slot, {$mapelCount} mapel — [$detail]");
        }
    }
}
