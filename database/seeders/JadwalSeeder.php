<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JadwalSeeder extends Seeder
{
    /**
     * Struktur data:
     * - semester_id = 1 (Ganjil, aktif)
     * - Kelas 10A–10J (id: 3–12)
     * - Senin–Jumat
     * - 1 JP = 45 menit
     * - Istirahat: 10:00–10:30 dan 12:00–13:00
     * - Jumat: kegiatan pagi 07:00–07:30, lalu 3 sesi mapel
     * - Senin–Kamis: 8 JP per hari (+ 1 sesi Penjas 2JP pagi)
     *
     * Penugasan guru (guru_id terkecil per mapel per kelas):
     * Diambil dari data real guru_mapel_kelas semester_id=1
     */

    // ── Konstanta waktu ────────────────────────────────────────
    // Sesi Senin–Kamis (45 menit per JP, istirahat 10:00–10:30, 12:00–13:00)
    // Slot:
    //  1: 07:00–07:45   JP1
    //  2: 07:45–08:30   JP2
    //  3: 08:30–09:15   JP3
    //  4: 09:15–10:00   JP4
    //  [ISTIRAHAT 10:00–10:30]
    //  5: 10:30–11:15   JP5
    //  6: 11:15–12:00   JP6
    //  [ISTIRAHAT 12:00–13:00]
    //  7: 13:00–13:45   JP7
    //  8: 13:45–14:30   JP8

    // Sesi Jumat (kegiatan 07:00–07:30, lalu mapel)
    //  1: 07:30–08:15   JP1
    //  2: 08:15–09:00   JP2
    //  3: 09:00–09:45   JP3
    //  [ISTIRAHAT 09:45–10:15]
    //  4: 10:15–11:00   JP4
    //  5: 11:00–11:45   JP5

    // ── Mapping penugasan: [kelas_id][mapel_id] => guru_id (terkecil) ──
    // Dibangun dari data tinker (pilih guru_id terkecil per kelas+mapel)

    private function getPenugasan(): array
    {
        // Format: kelas_id => [ mapel_id => guru_id ]
        // Dipilih guru_id terkecil dari data real

        $raw = [
            // PPKn (mapel_id=1): semua kelas → guru_id=17 (Drs. Masduki)
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

            // Matematika (mapel_id=2): guru_id=18 (Mutia Maisaroh) untuk 10A–10H
            // 10A ada 18,27,10,34 → pilih 10; 10B ada 18,27,10 → pilih 10
            // Dari data: kelas 3(10A)→18 terkecil, kelas 4(10B)→18 dst
            // Data penugasan: id=21 kelas=3 guru=18; id=22 kelas=4 guru=18 ...
            // Tapi ada juga id=220 kelas=3 guru=10, id=218 kelas=3 guru=27, id=230 kelas=3 guru=34
            // → pilih terkecil guru_id dari tiap kelas
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

            // Kimia (mapel_id=4): guru_id=30 (Evi Widianti) kelas 3–10
            [4, 3, 30],
            [4, 4, 30],
            [4, 5, 30],
            [4, 6, 30],
            [4, 7, 30],
            [4, 8, 30],
            [4, 9, 30],
            [4, 10, 30],

            // Sosiologi (mapel_id=6): guru_id=29 (Sri Sukmawati) kelas 3–8, guru=31 kelas 9–
            // Data: kelas3→29,31 terkecil=29; kelas4→29,31→29; ... kelas9→31 only; kelas10→31 only
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

            // Sejarah (mapel_id=8): guru_id=22 (Siti Nuraini) semua kelas
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

            // Seni Budaya (mapel_id=10): guru_id=12 (Inggit) & 19 (Wardiana)
            // Data: kelas3→19,12 terkecil=12; semua kelas ada 12 & 19 → pilih 12
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

            // Fisika (mapel_id=11): guru_id=5 (Suryani) semua kelas
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

            // Ekonomi (mapel_id=13): guru_id=20 (Suharto) semua kelas
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

            // Geografi (mapel_id=15): guru_id=21 (Hj. Euis Gardini) semua kelas
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

            // PJOK (mapel_id=16): guru_id terkecil per kelas
            // kelas 3(10A)→11,24,13,25 terkecil=11; kelas 4(10B)→11,24,13,25→11
            // kelas 9(10G)→11,24,13,25→11; kelas 10(10H)→11,24,25→11
            // kelas 11(10I)→tidak ada penugasan 11, ada 25,13? cek data: id=274 kelas=9→guru25; id=267 kelas=9→guru13
            // Dari data: kelas 11(10I) ada guru 13(id=267 kelas=9 bukan 11)...
            // Kelas 11(10I) & 12(10J): dari data penugasan hanya ada 10I & 10J di PJOK?
            // Check: id=76 kelas=10 guru=11; id=115 kelas=10 guru=24; id=275 kelas=10 guru=25
            // Tidak ada kelas 11 & 12 di PJOK dari data yg ada → skip atau gunakan guru terakhir
            // Dari data Drs. Ajat: kelas 10A-10H; Lia: 10A-10H; Irfan: 10A-10H; Freddy: 10A-10G
            // Jadi PJOK hanya sampai 10H untuk sebagian, tapi 10I & 10J ada Drs Ajat?
            // Data penugasan dari halaman: Drs Ajat → 10A-10H, Freddy→10A-10G, Irfan→10A-10H, Lia→10A-10H
            // Berarti 10I & 10J tidak punya guru PJOK → skip PJOK untuk 10I & 10J
            [16, 3, 11],
            [16, 4, 11],
            [16, 5, 11],
            [16, 6, 11],
            [16, 7, 11],
            [16, 8, 11],
            [16, 9, 11],
            [16, 10, 11],

            // Bahasa Inggris (mapel_id=17): guru_id=8 & 23, terkecil=8
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

            // Bahasa Indonesia (mapel_id=19): guru_id=9,33,37 → terkecil=9 untuk yg ada
            // Dari data: kelas3→37,9,33 terkecil=9; kelas4→37,9,33→9 ...
            // kelas 10(10H)→9,33,37→9; kelas 11(10I)? Popy→10A-10H, Dian→10A-10H, Kholijah→10A-10G
            // 10I & 10J: Dian dan Popy ada sampai 10H saja
            // Dari data penugasan halaman: Popy→10A-10H, Kholijah→10A-10G, Dian→10A-10H
            // Berarti 10I & 10J hanya punya Dian (sampai 10H)? Tidak ada B.Indo guru untuk 10I-10J?
            // Hmm dari halaman Dian→10A-10H, berarti 10I & 10J tidak punya B.Indo? Skip.
            [19, 3, 9],
            [19, 4, 9],
            [19, 5, 9],
            [19, 6, 9],
            [19, 7, 9],
            [19, 8, 9],
            [19, 9, 9],
            [19, 10, 9],

            // Bahasa Sunda (mapel_id=20): Kholijah(37)→10A-10D, Dian(33)→10A-10F
            // kelas3→37,33 terkecil=33; kelas4→37,33→33; kelas5→37,33→33; kelas6→37,33→33
            // kelas7→33 only; kelas8→33 only
            // kelas 9(10G)–12(10J): tidak ada penugasan B.Sunda → skip
            [20, 3, 33],
            [20, 4, 33],
            [20, 5, 33],
            [20, 6, 33],
            [20, 7, 33],
            [20, 8, 33],

            // PABP (mapel_id=21): guru_id=4 & 36, terkecil=4 semua kelas
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

            // Biologi (mapel_id=22): guru_id=6 (Nurliana) semua kelas
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

            // Prakarya (mapel_id=24): Evi Widianti(30) → 10A-10D only
            [24, 3, 30],
            [24, 4, 30],
            [24, 5, 30],
            [24, 6, 30],

            // Informatika (mapel_id=25): guru_id=35 (Galih) semua kelas
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

            // Bahasa Jepang (mapel_id=26): guru_id=32 (Asep) → 10A-10G
            [26, 3, 32],
            [26, 4, 32],
            [26, 5, 32],
            [26, 6, 32],
            [26, 7, 32],
            [26, 8, 32],
            [26, 9, 32],
        ];

        // Susun jadi $map[kelas_id][mapel_id] = guru_id
        $map = [];
        foreach ($raw as [$mapel_id, $kelas_id, $guru_id]) {
            $map[$kelas_id][$mapel_id] = $guru_id;
        }

        return $map;
    }

    // ── Slot waktu Senin–Kamis ─────────────────────────────────
    private function getSlotSenKam(): array
    {
        // [jam_mulai, jam_selesai, jp] — setiap sesi bisa 1, 2, atau 3 JP
        return [
            ['07:00', '07:45', 1],   // JP 1
            ['07:45', '08:30', 1],   // JP 2
            ['08:30', '09:15', 1],   // JP 3
            ['09:15', '10:00', 1],   // JP 4
            // istirahat 10:00–10:30
            ['10:30', '11:15', 1],   // JP 5
            ['11:15', '12:00', 1],   // JP 6
            // istirahat 12:00–13:00
            ['13:00', '13:45', 1],   // JP 7
            ['13:45', '14:30', 1],   // JP 8
        ];
    }

    // ── Slot waktu Jumat ───────────────────────────────────────
    private function getSlotJumat(): array
    {
        // Kegiatan 07:00–07:30 → mapel mulai 07:30
        return [
            ['07:30', '08:15', 1],   // JP 1
            ['08:15', '09:00', 1],   // JP 2
            ['09:00', '09:45', 1],   // JP 3
            // istirahat 09:45–10:15
            ['10:15', '11:00', 1],   // JP 4
            ['11:00', '11:45', 1],   // JP 5
        ];
    }

    // ── Jadwal mapel per hari per kelas ───────────────────────
    // Distribusi: tiap mapel idealnya 2–3 JP/minggu
    // Dengan 10 kelas dan constraint guru, kita susun per kelas
    // hari Senin–Kamis = 8 slot, Jumat = 5 slot → total 37 slot/kelas/minggu
    // ~15 mapel per kelas → rata-rata 2–3 JP/mapel

    /**
     * Mengembalikan jadwal per kelas: array of [hari, slot_index, mapel_id, jp]
     * jp = jumlah jam pelajaran (slot yang dipakai berurutan)
     *
     * Kita gunakan template yang sama untuk semua kelas,
     * lalu sesuaikan guru dari penugasan.
     *
     * Mapel per kelas 10A-10D (punya B.Sunda & Prakarya):
     *   PPKn(1)=2JP, MTK(2)=4JP, Kimia(4)=2JP, Sosiologi(6)=2JP,
     *   Sejarah(8)=2JP, SeniBudaya(10)=2JP, Fisika(11)=3JP,
     *   Ekonomi(13)=2JP, Geografi(15)=2JP, PJOK(16)=2JP,
     *   B.Inggris(17)=3JP, B.Indonesia(19)=4JP, B.Sunda(20)=2JP,
     *   PABP(21)=3JP, Biologi(22)=3JP, Prakarya(24)=2JP,
     *   Informatika(25)=2JP, B.Jepang(26)=2JP
     *   Total = 42JP → cocok (Senin-Kamis 8x4=32 + Jumat 5 = 37... kurangi)
     *   Kita pakai 37 slot dengan beberapa mapel 2JP & beberapa 3JP
     *
     * Mapel per kelas 10E-10F (punya B.Sunda tapi tidak Prakarya):
     *   Sama tapi tanpa Prakarya
     *
     * Mapel per kelas 10G (tidak punya B.Sunda, B.Indonesia sampai 10G, B.Jepang sampai 10G):
     *
     * Mapel per kelas 10H (tidak punya B.Sunda, tidak punya B.Jepang):
     *
     * Mapel per kelas 10I-10J (paling sedikit):
     *   Tidak punya: B.Indonesia, B.Sunda, PJOK, B.Jepang
     *   Punya: PPKn, MTK, Kimia(10I), Sosiologi, Sejarah, SeniBudaya,
     *          Fisika, Ekonomi, Geografi, B.Inggris, PABP, Biologi, Informatika
     *
     * Untuk simplifikasi, kita buat 3 template:
     *   A: kelas 10A-10D  (lengkap + B.Sunda + Prakarya + B.Jepang)
     *   B: kelas 10E-10G  (tanpa Prakarya, 10G tanpa B.Sunda)
     *   C: kelas 10H      (tanpa B.Sunda, tanpa Prakarya, tanpa B.Jepang)
     *   D: kelas 10I-10J  (paling sedikit)
     */
    private function getTemplateJadwal(int $kelasId): array
    {
        // Template: list of [hari, jam_mulai, jam_selesai, mapel_id]
        // Hari: Senin, Selasa, Rabu, Kamis, Jumat
        // Slot Senin-Kamis: 07:00,07:45,08:30,09:15,10:30,11:15,13:00,13:45
        // Slot Jumat:        07:30,08:15,09:00,10:15,11:00
        //
        // Kita susun manual untuk memastikan distribusi merata
        // Format: [hari, jam_mulai, jam_selesai, mapel_id]

        $mapel = [
            'ppkn' => 1,
            'mtk' => 2,
            'kimia' => 4,
            'sosiologi' => 6,
            'sejarah' => 8,
            'senibudaya' => 10,
            'fisika' => 11,
            'ekonomi' => 13,
            'geografi' => 15,
            'pjok' => 16,
            'bing' => 17,
            'bind' => 19,
            'bsunda' => 20,
            'pabp' => 21,
            'biologi' => 22,
            'prakarya' => 24,
            'info' => 25,
            'bjepang' => 26,
        ];

        // Template A: 10A, 10B, 10C, 10D (semua mapel)
        $templateA = [
            // SENIN
            ['Senin', '07:00', '07:45', $mapel['pabp']],
            ['Senin', '07:45', '08:30', $mapel['pabp']],
            ['Senin', '08:30', '09:15', $mapel['mtk']],
            ['Senin', '09:15', '10:00', $mapel['mtk']],
            ['Senin', '10:30', '11:15', $mapel['bind']],
            ['Senin', '11:15', '12:00', $mapel['bind']],
            ['Senin', '13:00', '13:45', $mapel['fisika']],
            ['Senin', '13:45', '14:30', $mapel['fisika']],
            // SELASA
            ['Selasa', '07:00', '07:45', $mapel['bing']],
            ['Selasa', '07:45', '08:30', $mapel['bing']],
            ['Selasa', '08:30', '09:15', $mapel['ppkn']],
            ['Selasa', '09:15', '10:00', $mapel['ppkn']],
            ['Selasa', '10:30', '11:15', $mapel['sejarah']],
            ['Selasa', '11:15', '12:00', $mapel['sejarah']],
            ['Selasa', '13:00', '13:45', $mapel['ekonomi']],
            ['Selasa', '13:45', '14:30', $mapel['biologi']],
            // RABU
            ['Rabu', '07:00', '07:45', $mapel['geografi']],
            ['Rabu', '07:45', '08:30', $mapel['geografi']],
            ['Rabu', '08:30', '09:15', $mapel['kimia']],
            ['Rabu', '09:15', '10:00', $mapel['kimia']],
            ['Rabu', '10:30', '11:15', $mapel['bsunda']],
            ['Rabu', '11:15', '12:00', $mapel['bsunda']],
            ['Rabu', '13:00', '13:45', $mapel['info']],
            ['Rabu', '13:45', '14:30', $mapel['info']],
            // KAMIS
            ['Kamis', '07:00', '07:45', $mapel['sosiologi']],
            ['Kamis', '07:45', '08:30', $mapel['sosiologi']],
            ['Kamis', '08:30', '09:15', $mapel['mtk']],
            ['Kamis', '09:15', '10:00', $mapel['mtk']],
            ['Kamis', '10:30', '11:15', $mapel['pjok']],
            ['Kamis', '11:15', '12:00', $mapel['pjok']],
            ['Kamis', '13:00', '13:45', $mapel['prakarya']],
            ['Kamis', '13:45', '14:30', $mapel['prakarya']],
            // JUMAT
            ['Jumat', '07:30', '08:15', $mapel['bind']],
            ['Jumat', '08:15', '09:00', $mapel['bind']],
            ['Jumat', '09:00', '09:45', $mapel['senibudaya']],
            ['Jumat', '10:15', '11:00', $mapel['biologi']],
            ['Jumat', '11:00', '11:45', $mapel['bjepang']],
        ];

        // Template B1: 10E (punya B.Sunda, tidak punya Prakarya, ada B.Jepang)
        $templateB1 = [
            ['Senin', '07:00', '07:45', $mapel['pabp']],
            ['Senin', '07:45', '08:30', $mapel['pabp']],
            ['Senin', '08:30', '09:15', $mapel['mtk']],
            ['Senin', '09:15', '10:00', $mapel['mtk']],
            ['Senin', '10:30', '11:15', $mapel['bind']],
            ['Senin', '11:15', '12:00', $mapel['bind']],
            ['Senin', '13:00', '13:45', $mapel['fisika']],
            ['Senin', '13:45', '14:30', $mapel['fisika']],
            ['Selasa', '07:00', '07:45', $mapel['bing']],
            ['Selasa', '07:45', '08:30', $mapel['bing']],
            ['Selasa', '08:30', '09:15', $mapel['ppkn']],
            ['Selasa', '09:15', '10:00', $mapel['ppkn']],
            ['Selasa', '10:30', '11:15', $mapel['sejarah']],
            ['Selasa', '11:15', '12:00', $mapel['sejarah']],
            ['Selasa', '13:00', '13:45', $mapel['ekonomi']],
            ['Selasa', '13:45', '14:30', $mapel['biologi']],
            ['Rabu', '07:00', '07:45', $mapel['geografi']],
            ['Rabu', '07:45', '08:30', $mapel['geografi']],
            ['Rabu', '08:30', '09:15', $mapel['kimia']],
            ['Rabu', '09:15', '10:00', $mapel['kimia']],
            ['Rabu', '10:30', '11:15', $mapel['bsunda']],
            ['Rabu', '11:15', '12:00', $mapel['bsunda']],
            ['Rabu', '13:00', '13:45', $mapel['info']],
            ['Rabu', '13:45', '14:30', $mapel['info']],
            ['Kamis', '07:00', '07:45', $mapel['sosiologi']],
            ['Kamis', '07:45', '08:30', $mapel['sosiologi']],
            ['Kamis', '08:30', '09:15', $mapel['mtk']],
            ['Kamis', '09:15', '10:00', $mapel['mtk']],
            ['Kamis', '10:30', '11:15', $mapel['pjok']],
            ['Kamis', '11:15', '12:00', $mapel['pjok']],
            ['Kamis', '13:00', '13:45', $mapel['bjepang']],  // ganti prakarya
            ['Kamis', '13:45', '14:30', $mapel['senibudaya']],
            ['Jumat', '07:30', '08:15', $mapel['bind']],
            ['Jumat', '08:15', '09:00', $mapel['bind']],
            ['Jumat', '09:00', '09:45', $mapel['biologi']],
            ['Jumat', '10:15', '11:00', $mapel['ekonomi']],
            ['Jumat', '11:00', '11:45', $mapel['pabp']],
        ];

        // Template B2: 10F (punya B.Sunda s.d 10F, tidak punya Prakarya, ada B.Jepang)
        $templateB2 = $templateB1; // sama dengan B1

        // Template B3: 10G (tidak punya B.Sunda, tidak punya Prakarya, ada B.Jepang)
        $templateB3 = [
            ['Senin', '07:00', '07:45', $mapel['pabp']],
            ['Senin', '07:45', '08:30', $mapel['pabp']],
            ['Senin', '08:30', '09:15', $mapel['mtk']],
            ['Senin', '09:15', '10:00', $mapel['mtk']],
            ['Senin', '10:30', '11:15', $mapel['bind']],
            ['Senin', '11:15', '12:00', $mapel['bind']],
            ['Senin', '13:00', '13:45', $mapel['fisika']],
            ['Senin', '13:45', '14:30', $mapel['fisika']],
            ['Selasa', '07:00', '07:45', $mapel['bing']],
            ['Selasa', '07:45', '08:30', $mapel['bing']],
            ['Selasa', '08:30', '09:15', $mapel['ppkn']],
            ['Selasa', '09:15', '10:00', $mapel['ppkn']],
            ['Selasa', '10:30', '11:15', $mapel['sejarah']],
            ['Selasa', '11:15', '12:00', $mapel['sejarah']],
            ['Selasa', '13:00', '13:45', $mapel['ekonomi']],
            ['Selasa', '13:45', '14:30', $mapel['biologi']],
            ['Rabu', '07:00', '07:45', $mapel['geografi']],
            ['Rabu', '07:45', '08:30', $mapel['geografi']],
            ['Rabu', '08:30', '09:15', $mapel['kimia']],
            ['Rabu', '09:15', '10:00', $mapel['kimia']],
            ['Rabu', '10:30', '11:15', $mapel['sosiologi']],  // ganti bsunda
            ['Rabu', '11:15', '12:00', $mapel['sosiologi']],
            ['Rabu', '13:00', '13:45', $mapel['info']],
            ['Rabu', '13:45', '14:30', $mapel['info']],
            ['Kamis', '07:00', '07:45', $mapel['bjepang']],
            ['Kamis', '07:45', '08:30', $mapel['senibudaya']],
            ['Kamis', '08:30', '09:15', $mapel['mtk']],
            ['Kamis', '09:15', '10:00', $mapel['mtk']],
            ['Kamis', '10:30', '11:15', $mapel['pjok']],
            ['Kamis', '11:15', '12:00', $mapel['pjok']],
            ['Kamis', '13:00', '13:45', $mapel['pabp']],
            ['Kamis', '13:45', '14:30', $mapel['ekonomi']],
            ['Jumat', '07:30', '08:15', $mapel['bind']],
            ['Jumat', '08:15', '09:00', $mapel['bind']],
            ['Jumat', '09:00', '09:45', $mapel['biologi']],
            ['Jumat', '10:15', '11:00', $mapel['geografi']],
            ['Jumat', '11:00', '11:45', $mapel['fisika']],
        ];

        // Template C: 10H (tidak punya B.Sunda, tidak punya Prakarya, tidak punya B.Jepang)
        $templateC = [
            ['Senin', '07:00', '07:45', $mapel['pabp']],
            ['Senin', '07:45', '08:30', $mapel['pabp']],
            ['Senin', '08:30', '09:15', $mapel['mtk']],
            ['Senin', '09:15', '10:00', $mapel['mtk']],
            ['Senin', '10:30', '11:15', $mapel['bind']],
            ['Senin', '11:15', '12:00', $mapel['bind']],
            ['Senin', '13:00', '13:45', $mapel['fisika']],
            ['Senin', '13:45', '14:30', $mapel['fisika']],
            ['Selasa', '07:00', '07:45', $mapel['bing']],
            ['Selasa', '07:45', '08:30', $mapel['bing']],
            ['Selasa', '08:30', '09:15', $mapel['ppkn']],
            ['Selasa', '09:15', '10:00', $mapel['ppkn']],
            ['Selasa', '10:30', '11:15', $mapel['sejarah']],
            ['Selasa', '11:15', '12:00', $mapel['sejarah']],
            ['Selasa', '13:00', '13:45', $mapel['ekonomi']],
            ['Selasa', '13:45', '14:30', $mapel['biologi']],
            ['Rabu', '07:00', '07:45', $mapel['geografi']],
            ['Rabu', '07:45', '08:30', $mapel['geografi']],
            ['Rabu', '08:30', '09:15', $mapel['kimia']],
            ['Rabu', '09:15', '10:00', $mapel['kimia']],
            ['Rabu', '10:30', '11:15', $mapel['sosiologi']],
            ['Rabu', '11:15', '12:00', $mapel['sosiologi']],
            ['Rabu', '13:00', '13:45', $mapel['info']],
            ['Rabu', '13:45', '14:30', $mapel['info']],
            ['Kamis', '07:00', '07:45', $mapel['senibudaya']],
            ['Kamis', '07:45', '08:30', $mapel['biologi']],
            ['Kamis', '08:30', '09:15', $mapel['mtk']],
            ['Kamis', '09:15', '10:00', $mapel['mtk']],
            ['Kamis', '10:30', '11:15', $mapel['pjok']],
            ['Kamis', '11:15', '12:00', $mapel['pjok']],
            ['Kamis', '13:00', '13:45', $mapel['pabp']],
            ['Kamis', '13:45', '14:30', $mapel['ekonomi']],
            ['Jumat', '07:30', '08:15', $mapel['bind']],
            ['Jumat', '08:15', '09:00', $mapel['bind']],
            ['Jumat', '09:00', '09:45', $mapel['fisika']],
            ['Jumat', '10:15', '11:00', $mapel['geografi']],
            ['Jumat', '11:00', '11:45', $mapel['ppkn']],
        ];

        // Template D: 10I & 10J (tidak punya B.Indo, B.Sunda, PJOK, B.Jepang, Prakarya, Kimia(10J))
        // 10I: PPKn, MTK, Kimia, Sosiologi, Sejarah, SeniBudaya, Fisika, Ekonomi, Geografi, B.Inggris, PABP, Biologi, Informatika
        // 10J: sama tapi cek kimia — dari data penugasan kelas 11(10I) dan 12(10J):
        //   Kimia: data kelas_id=11(10I) tidak ada di raw (Evi hanya s.d kelas_id=10 yaitu 10H)
        //   Wait: kelas_id=11 = 10I, kelas_id=12 = 10J
        //   Dari data Evi: id=177 kelas=10, id=176 kelas=9 → maks kelas_id=10 (10H)
        //   Jadi 10I & 10J tidak punya Kimia → skip
        $templateD = [
            ['Senin', '07:00', '07:45', $mapel['pabp']],
            ['Senin', '07:45', '08:30', $mapel['pabp']],
            ['Senin', '08:30', '09:15', $mapel['mtk']],
            ['Senin', '09:15', '10:00', $mapel['mtk']],
            ['Senin', '10:30', '11:15', $mapel['bing']],
            ['Senin', '11:15', '12:00', $mapel['bing']],
            ['Senin', '13:00', '13:45', $mapel['fisika']],
            ['Senin', '13:45', '14:30', $mapel['fisika']],
            ['Selasa', '07:00', '07:45', $mapel['ppkn']],
            ['Selasa', '07:45', '08:30', $mapel['ppkn']],
            ['Selasa', '08:30', '09:15', $mapel['sejarah']],
            ['Selasa', '09:15', '10:00', $mapel['sejarah']],
            ['Selasa', '10:30', '11:15', $mapel['ekonomi']],
            ['Selasa', '11:15', '12:00', $mapel['ekonomi']],
            ['Selasa', '13:00', '13:45', $mapel['biologi']],
            ['Selasa', '13:45', '14:30', $mapel['biologi']],
            ['Rabu', '07:00', '07:45', $mapel['geografi']],
            ['Rabu', '07:45', '08:30', $mapel['geografi']],
            ['Rabu', '08:30', '09:15', $mapel['sosiologi']],
            ['Rabu', '09:15', '10:00', $mapel['sosiologi']],
            ['Rabu', '10:30', '11:15', $mapel['info']],
            ['Rabu', '11:15', '12:00', $mapel['info']],
            ['Rabu', '13:00', '13:45', $mapel['senibudaya']],
            ['Rabu', '13:45', '14:30', $mapel['senibudaya']],
            ['Kamis', '07:00', '07:45', $mapel['mtk']],
            ['Kamis', '07:45', '08:30', $mapel['mtk']],
            ['Kamis', '08:30', '09:15', $mapel['pabp']],
            ['Kamis', '09:15', '10:00', $mapel['fisika']],
            ['Kamis', '10:30', '11:15', $mapel['ppkn']],
            ['Kamis', '11:15', '12:00', $mapel['sejarah']],
            ['Kamis', '13:00', '13:45', $mapel['biologi']],
            ['Kamis', '13:45', '14:30', $mapel['ekonomi']],
            ['Jumat', '07:30', '08:15', $mapel['bing']],
            ['Jumat', '08:15', '09:00', $mapel['geografi']],
            ['Jumat', '09:00', '09:45', $mapel['sosiologi']],
            ['Jumat', '10:15', '11:00', $mapel['info']],
            ['Jumat', '11:00', '11:45', $mapel['senibudaya']],
        ];

        return match ($kelasId) {
            3, 4, 5, 6 => $templateA,   // 10A, 10B, 10C, 10D
            7, 8 => $templateB1,  // 10E, 10F
            9 => $templateB3,  // 10G
            10 => $templateC,   // 10H
            11, 12 => $templateD,   // 10I, 10J
            default => $templateA,
        };
    }

    public function run(): void
    {
        $semesterId = 1;
        $now = Carbon::now();
        $penugasan = $this->getPenugasan();

        // ── 1. Jadwal Pelajaran ────────────────────────────────
        $jadwals = [];

        $kelasList = [3, 4, 5, 6, 7, 8, 9, 10, 11, 12]; // 10A–10J

        foreach ($kelasList as $kelasId) {
            $template = $this->getTemplateJadwal($kelasId);

            foreach ($template as [$hari, $jamMulai, $jamSelesai, $mapelId]) {
                // Cek apakah penugasan ada untuk kelas+mapel ini
                if (! isset($penugasan[$kelasId][$mapelId])) {
                    // Skip jika tidak ada guru yang ditugaskan
                    continue;
                }

                $guruId = $penugasan[$kelasId][$mapelId];

                $jadwals[] = [
                    'semester_id' => $semesterId,
                    'kelas_id' => $kelasId,
                    'mapel_id' => $mapelId,
                    'guru_id' => $guruId,
                    'hari' => $hari,
                    'jam_mulai' => $jamMulai,
                    'jam_selesai' => $jamSelesai,
                    'is_active' => true,
                    'catatan' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // Insert in chunks
        foreach (array_chunk($jadwals, 100) as $chunk) {
            DB::table('jadwals')->insert($chunk);
        }

        $this->command->info('✅ Jadwal pelajaran berhasil di-seed: '.count($jadwals).' baris');

        // ── 2. Jadwal Kegiatan Jumat ───────────────────────────
        // 4 kegiatan (minggu ke-1 s.d ke-4), jam 07:00–07:30
        $kegiatanList = [
            1 => 'Upacara Bendera',
            2 => 'Senam Bersama',
            3 => 'Jumat Bersih',
            4 => 'Literasi',
        ];

        $kegiatans = [];
        foreach ($kegiatanList as $mingguKe => $namaKegiatan) {
            $kegiatans[] = [
                'semester_id' => $semesterId,
                'hari' => 'Jumat',
                'minggu_ke' => $mingguKe,
                'nama_kegiatan' => $namaKegiatan,
                'jam_mulai' => '07:00',
                'jam_selesai' => '07:30',
                'is_active' => true,
                'catatan' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('jadwal_kegiatans')->insert($kegiatans);

        $this->command->info('✅ Jadwal kegiatan Jumat berhasil di-seed: '.count($kegiatans).' baris');
        $this->command->info('');
        $this->command->info('📋 Ringkasan jadwal pelajaran per kelas:');

        $byKelas = collect($jadwals)->groupBy('kelas_id');
        $kelasNama = [3 => '10A', 4 => '10B', 5 => '10C', 6 => '10D', 7 => '10E', 8 => '10F', 9 => '10G', 10 => '10H', 11 => '10I', 12 => '10J'];
        foreach ($byKelas as $kId => $rows) {
            $mapelCount = collect($rows)->pluck('mapel_id')->unique()->count();
            $this->command->line("   Kelas {$kelasNama[$kId]}: {$rows->count()} slot, {$mapelCount} mapel");
        }
    }
}
