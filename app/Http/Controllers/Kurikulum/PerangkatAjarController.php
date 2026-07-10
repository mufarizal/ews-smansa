<?php

namespace App\Http\Controllers\Kurikulum;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\GuruMapelKelas;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Semester;
use Illuminate\Http\Request;

class PerangkatAjarController extends Controller
{
    /**
     * Index: menampilkan satu kartu per kombinasi Mapel + Guru yang
     * SUDAH memiliki minimal 1 bab. Kelas-kelas paralel (10A, 10B, dst)
     * digabung jadi satu kartu, tidak diulang per kelas.
     */
    public function index(Request $request)
    {
        $semesterId = $request->filled('semester_id') ? (int) $request->semester_id : null;
        $kelasId = $request->filled('kelas_id') ? (int) $request->kelas_id : null;
        $mapelId = $request->filled('mapel_id') ? (int) $request->mapel_id : null;
        $guruId = $request->filled('guru_id') ? (int) $request->guru_id : null;
        $search = trim((string) $request->get('search', ''));

        $query = GuruMapelKelas::query()
            ->with([
                'mapel',
                'kelas',
                'guru',
                'semester',
                'babs' => fn($q) => $q->orderBy('urutan'),
                'babs.materi',
            ])
            // Kunci utama: hanya ambil yang sudah punya bab
            ->whereHas('babs');

        if ($semesterId) {
            $query->where('semester_id', $semesterId);
        }
        if ($kelasId) {
            $query->where('kelas_id', $kelasId);
        }
        if ($mapelId) {
            $query->where('mapel_id', $mapelId);
        }
        if ($guruId) {
            $query->where('guru_id', $guruId);
        }
        if ($search !== '') {
            $query->whereHas('babs', function ($q) use ($search) {
                $q->where('nama_bab', 'like', "%{$search}%")
                    ->orWhereHas('materi', fn($mq) => $mq->where('judul', 'like', "%{$search}%"));
            });
        }

        $rows = $query->get();

        // Group semua baris GuruMapelKelas berdasarkan Mapel + Guru,
        // supaya kelas paralel tidak menghasilkan kartu berulang.
        $mapelCards = $rows
            ->groupBy(fn($gmk) => $gmk->mapel_id . '-' . $gmk->guru_id)
            ->map(function ($group) {
                $first = $group->first();

                // Bab unik berdasarkan nama_bab (konten biasanya sama di tiap kelas paralel)
                $allBabs = $group->flatMap(fn($gmk) => $gmk->babs);
                $uniqueBabs = $allBabs->unique('nama_bab');

                $uniqueMateriCount = $allBabs
                    ->flatMap(fn($bab) => $bab->materi)
                    ->unique('judul')
                    ->count();

                return (object) [
                    'mapel_id' => $first->mapel_id,
                    'guru_id' => $first->guru_id,
                    'mapel' => $first->mapel,
                    'guru' => $first->guru,
                    'semesterNames' => $group->pluck('semester.nama')->filter()->unique()->values(),
                    'kelasNames' => $group->pluck('kelas.nama_kelas')->filter()->unique()->sort()->values(),
                    'babCount' => $uniqueBabs->count(),
                    'materiCount' => $uniqueMateriCount,
                    'kelasCount' => $group->pluck('kelas_id')->unique()->count(),
                ];
            })
            ->sortBy(fn($card) => $card->mapel->nama ?? '')
            ->values();

        return view('kurikulum.perangkat-ajar.index', [
            'mapelCards' => $mapelCards,
            'semesterList' => Semester::orderByDesc('is_active')->orderByDesc('id')->get(),
            'kelasList' => Kelas::orderBy('nama_kelas')->get(),
            'mapelList' => Mapel::orderBy('nama')->get(),
            'guruList' => Guru::orderBy('nama')->get(),
            'filters' => [
                'semester_id' => $semesterId,
                'kelas_id' => $kelasId,
                'mapel_id' => $mapelId,
                'guru_id' => $guruId,
                'search' => $search,
            ],
            'activeSemester' => Semester::where('is_active', true)->first(),
        ]);
    }

    /**
     * Show: detail bab & materi untuk satu Mapel + Guru, dikelompokkan
     * per kelas (karena tiap kelas punya baris GuruMapelKelas & bab sendiri).
     */
    public function show(Mapel $mapel, Guru $guru, Request $request)
    {
        $semesterId = $request->filled('semester_id') ? (int) $request->semester_id : null;

        $query = GuruMapelKelas::query()
            ->where('mapel_id', $mapel->id)
            ->where('guru_id', $guru->id)
            ->with([
                'kelas',
                'semester',
                'babs' => fn($q) => $q->orderBy('urutan'),
                'babs.materi' => fn($q) => $q->orderBy('urutan'),
            ])
            ->whereHas('babs');

        if ($semesterId) {
            $query->where('semester_id', $semesterId);
        }

        $kelasGroups = $query->get()
            ->sortBy(fn($gmk) => $gmk->kelas->nama_kelas ?? '')
            ->values();

        return view('kurikulum.perangkat-ajar.show', [
            'mapel' => $mapel,
            'guru' => $guru,
            'kelasGroups' => $kelasGroups,
        ]);
    }
}