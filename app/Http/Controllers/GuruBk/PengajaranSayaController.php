<?php

namespace App\Http\Controllers\GuruBk;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PengajaranSayaController extends Controller
{
    public function index(Request $request)
    {
        $activeSemester = Semester::where('is_active', true)->first();
        $selectedSemesterId = $request->filled('semester_id')
            ? (int) $request->semester_id
            : $activeSemester?->id;
        $todayHari = Jadwal::carbonToHari(now());

        $user = Auth::user();
        if (!$user?->guru) {
            return view('guru_bk.pengajaran_saya.index', $this->emptyData($selectedSemesterId, $todayHari));
        }

        $guru = Guru::with([
            'user',
            'guruBkKelas' => fn($query) => $query
                ->with(['kelas', 'semester'])
                ->when($selectedSemesterId, fn($q) => $q->where('semester_id', $selectedSemesterId)),
        ])->find($user->guru->id);

        if (!$guru) {
            return view('guru_bk.pengajaran_saya.index', $this->emptyData($selectedSemesterId, $todayHari));
        }

        $kelasIds = $guru->guruBkKelas->pluck('kelas_id');

        $kelasDiajar = Kelas::with([
            'semester',
            'waliKelas',
            'siswas' => fn($query) => $query->orderBy('nama'),
        ])
            ->whereIn('id', $kelasIds)
            ->orderBy('nama_kelas')
            ->get();

        $jadwals = Jadwal::with(['semester', 'kelas', 'mapel', 'guru'])
            ->where('guru_id', $guru->id)
            ->when($selectedSemesterId, fn($query) => $query->where('semester_id', $selectedSemesterId))
            ->orderByRaw("CASE hari
                WHEN 'Senin'  THEN 1 WHEN 'Selasa' THEN 2 WHEN 'Rabu'   THEN 3
                WHEN 'Kamis'  THEN 4 WHEN 'Jumat'  THEN 5 WHEN 'Sabtu'  THEN 6
                ELSE 99 END")
            ->orderBy('jam_mulai')
            ->paginate(10)
            ->withQueryString();

        $jadwalHariIni = Jadwal::with(['semester', 'kelas', 'mapel'])
            ->where('guru_id', $guru->id)
            ->where('hari', $todayHari)
            ->when($selectedSemesterId, fn($query) => $query->where('semester_id', $selectedSemesterId))
            ->orderBy('jam_mulai')
            ->get();

        return view('guru_bk.pengajaran_saya.index', [
            'guru' => $guru,
            'kelasDiajar' => $kelasDiajar,
            'assignments' => $guru->guruBkKelas,
            'jadwals' => $jadwals,
            'jadwalPerHari' => $jadwals->getCollection()->groupBy('hari'),
            'jadwalHariIni' => $jadwalHariIni,
            'activeSemester' => $activeSemester,
            'semesterList' => Semester::orderByDesc('is_active')->orderByDesc('id')->get(),
            'selectedSemesterId' => $selectedSemesterId,
            'todayHari' => $todayHari,
        ]);
    }

    private function emptyData(?int $selectedSemesterId, string $todayHari): array
    {
        return [
            'guru' => null,
            'kelasDiajar' => collect(),
            'assignments' => collect(),
            'jadwals' => collect(),
            'jadwalPerHari' => collect(),
            'jadwalHariIni' => collect(),
            'activeSemester' => Semester::where('is_active', true)->first(),
            'semesterList' => Semester::orderByDesc('is_active')->orderByDesc('id')->get(),
            'selectedSemesterId' => $selectedSemesterId,
            'todayHari' => $todayHari,
        ];
    }
}
