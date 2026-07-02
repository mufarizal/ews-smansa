<?php

return [

    'bobot' => [
        'c1_akademik' => 0.40,  // 40%
        'c2_absensi' => 0.35,  // 35%
        'c3_perilaku' => 0.25,  // 25%
    ],

    'threshold' => [
        'aman' => 0.70,  // Skor >= 0.70 → Aman
        'perhatian' => 0.45,  // Skor >= 0.45 → Perhatian, < 0.45 → Binaan
    ],

    'absensi' => [
        'minimal_persen' => 90,   // Kehadiran minimal 90% untuk dianggap aman
        'alpha_max_tahun' => 15,   // Maksimal 15 hari alpha per tahun (dok. kurikulum)
        'alpha_max_semester' => 8, // Asumsi per semester (~15/2 dibulatkan ke atas)
    ],

    'akademik' => [
        'kkm' => 76,  // Nilai KKM — update setelah konfirmasi sekolah
    ],

    'scheduler' => [
        'jam_hitung' => '23:00',  // Scheduler jalan tiap hari jam 23:00
    ],

];