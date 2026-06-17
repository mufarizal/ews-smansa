<?php

namespace App\Http\Controllers\GuruMapel;

use App\Http\Controllers\Controller;
use App\Models\GuruMapelKelas;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Semester;
use Illuminate\Http\Request;

class PengajaranSayaController extends Controller
{
    public function index(Request $request)
    {
        $guru = auth()->user()?->guru;

        if (!$guru) {
            return view('guru_mapel.pengajaran_saya.index', [
                'guru' => null,
                'mapels' => collect(),
                'kelasDiajar' => collect(),
                'assignments' => collect(),
                'jadwals' => collect(),
                'jadwalPerHari' => collect(),
                'jadwalHariIni' => collect(),
                'activeSemester' => null,
                'semesterList' => collect(),
                'selectedSemesterId' => null,
                'todayHari' => Jadwal::carbonToHari(now()),
            ]);
        }

        $activeSemester = Semester::where('is_active', true)->first();
        $selectedSemesterId = $request->filled('semester_id')
            ? (int) $request->semester_id
            : $activeSemester?->id;

        // Assignments with relations
        $assignments = GuruMapelKelas::with(['mapel', 'kelas', 'semester'])
            ->where('guru_id', $guru->id)
            ->when($selectedSemesterId, fn($q) => $q->where('semester_id', $selectedSemesterId))
            ->orderByDesc('semester_id')
            ->orderBy('kelas_id')
            ->orderBy('mapel_id')
            ->get();

        $mapels = $assignments->map(fn($a) => $a->mapel)
            ->filter()
            ->unique('id')
            ->values();

        // Load kelas with siswas (ordered by nama) in one query — no N+1
        $kelasIds = $assignments->pluck('kelas_id')->filter()->unique()->values();
        $kelasDiajar = Kelas::with(['siswas' => fn($q) => $q->orderBy('nama')])
            ->whereIn('id', $kelasIds)
            ->get();

        // Re-attach hydrated kelas (with siswas) onto each assignment
        $kelasMap = $kelasDiajar->keyBy('id');
        $assignments->each(function ($a) use ($kelasMap) {
            if ($a->kelas_id && $kelasMap->has($a->kelas_id)) {
                $a->setRelation('kelas', $kelasMap->get($a->kelas_id));
            }
        });

        // Schedules — paginated, grouped by day for the view
        $jadwals = Jadwal::with(['semester', 'kelas', 'mapel', 'guru'])
            ->where('guru_id', $guru->id)
            ->when($selectedSemesterId, fn($q) => $q->where('semester_id', $selectedSemesterId))
            ->orderByRaw("CASE hari
                WHEN 'Senin'  THEN 1 WHEN 'Selasa' THEN 2 WHEN 'Rabu'   THEN 3
                WHEN 'Kamis'  THEN 4 WHEN 'Jumat'  THEN 5 WHEN 'Sabtu'  THEN 6
                ELSE 99 END")
            ->orderBy('jam_mulai')
            ->paginate(25)
            ->withQueryString();

        $jadwalHariIni = Jadwal::with(['semester', 'kelas', 'mapel'])
            ->where('guru_id', $guru->id)
            ->where('hari', Jadwal::carbonToHari(now()))
            ->when($selectedSemesterId, fn($q) => $q->where('semester_id', $selectedSemesterId))
            ->orderBy('jam_mulai')
            ->get();

        return view('guru_mapel.pengajaran_saya.index', [
            'guru' => $guru,
            'mapels' => $mapels,
            'kelasDiajar' => $kelasDiajar,
            'assignments' => $assignments,
            'jadwals' => $jadwals,
            'jadwalPerHari' => $jadwals->getCollection()->groupBy('hari'),
            'jadwalHariIni' => $jadwalHariIni,
            'activeSemester' => $activeSemester,
            'semesterList' => Semester::orderByDesc('is_active')->orderByDesc('id')->get(),
            'selectedSemesterId' => $selectedSemesterId,
            'todayHari' => Jadwal::carbonToHari(now()),
        ]);
    }
}