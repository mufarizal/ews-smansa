<?php

namespace App\Http\Controllers\Kurikulum;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Semester;
use App\Models\Siswa;

class DashboardController extends Controller
{
    public function index()
    {
        $activeSemester = Semester::where('is_active', true)->first();

        $summary = [
            'totalMapel' => Mapel::count(),
            'totalKelas' => Kelas::count(),
            'totalGuru' => Guru::count(),
            'totalSiswa' => Siswa::count(),
        ];

        $jadwalHariIni = collect();
        if ($activeSemester) {
            $todayHari = Jadwal::carbonToHari(now());
            $jadwalHariIni = Jadwal::with(['kelas', 'guru', 'mapel'])
                ->where('semester_id', $activeSemester->id)
                ->where('is_active', true)
                ->where('hari', $todayHari)
                ->orderBy('jam_mulai')
                ->get();
        }

        return view('kurikulum.dashboard', [
            'activeSemester' => $activeSemester,
            'summary' => $summary,
            'jadwalHariIni' => $jadwalHariIni,
            'todayHari' => Jadwal::carbonToHari(now()),
        ]);
    }
}
