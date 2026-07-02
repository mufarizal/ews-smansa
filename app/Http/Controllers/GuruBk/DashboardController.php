<?php

namespace App\Http\Controllers\GuruBk;

use App\Http\Controllers\Controller;
use App\Models\EarlyWarningResult;
use App\Models\GuruBkKelas;
use App\Models\Semester;
use App\Service\EwsSnapshotService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(private EwsSnapshotService $snapshotService)
    {
    }

    private function getGuruId(): int
    {
        return Auth::user()->guru->id;
    }

    public function index()
    {
        $semester = Semester::where('is_active', true)->firstOrFail();
        $guruId = $this->getGuruId();

        // Kelas yang diampu di semester aktif
        $guruBkKelas = GuruBkKelas::with('kelas')
            ->where('guru_id', $guruId)
            ->where('semester_id', $semester->id)
            ->get();

        $kelasIds = $guruBkKelas->pluck('kelas_id');

        // Semua hasil SAW (snapshot TERBARU tiap kelas) dari kelas yang diampu,
        // dipakai untuk ringkasan global.
        $semuaHasilTerbaru = $kelasIds->flatMap(
            fn($kelasId) => $this->snapshotService->latestSnapshotPerKelas($kelasId, $semester->id)
        );

        $ringkasan = [
            'binaan' => $semuaHasilTerbaru->where('kategori', 'binaan')->count(),
            'perhatian' => $semuaHasilTerbaru->where('kategori', 'perhatian')->count(),
            'aman' => $semuaHasilTerbaru->where('kategori', 'aman')->count(),
            'total' => $semuaHasilTerbaru->count(),
        ];

        // 5 siswa terburuk PER KELAS (bukan gabungan lintas kelas) + trend
        $ringkasanPerKelas = $guruBkKelas->mapWithKeys(function ($gbk) use ($semester) {
            $snapshot = $this->snapshotService->latestSnapshotPerKelas($gbk->kelas_id, $semester->id);

            if ($snapshot->isEmpty()) {
                return [
                    $gbk->kelas_id => [
                        'kelas' => $gbk->kelas,
                        'siswa_terburuk' => collect(),
                        'trend_mingguan' => null,
                    ]
                ];
            }

            $siswaTerburuk = $this->snapshotService->urutkanPrioritas($snapshot)
                ->take(5)
                ->map(function ($item) use ($semester) {
                    $item->trend_harian = $this->snapshotService->trendHarian(
                        $item->siswa_id,
                        $semester->id,
                        $item->tanggal_hitung
                    );
                    return $item;
                });

            return [
                $gbk->kelas_id => [
                    'kelas' => $gbk->kelas,
                    'siswa_terburuk' => $siswaTerburuk,
                    'trend_mingguan' => $this->snapshotService->trendMingguan($gbk->kelas_id, $semester->id),
                ]
            ];
        });

        // Status generate per kelas (kesehatan scheduler, BUKAN generate manual)
        $hasilPerKelas = EarlyWarningResult::where('semester_id', $semester->id)
            ->whereIn('kelas_id', $kelasIds)
            ->selectRaw('kelas_id, COUNT(*) as total_siswa, MAX(generated_at) as last_generated_at')
            ->groupBy('kelas_id')
            ->get()
            ->keyBy('kelas_id');

        // Kelas yang belum pernah di-generate
        $kelasBelumGenerate = $guruBkKelas->filter(
            fn($gbk) => !isset($hasilPerKelas[$gbk->kelas_id])
        );

        // Kelas yang sudah generate tapi > 30 hari lalu
        $kelasStale = $guruBkKelas->filter(function ($gbk) use ($hasilPerKelas) {
            $info = $hasilPerKelas[$gbk->kelas_id] ?? null;
            return $info && now()->diffInDays($info->last_generated_at) > 30;
        });

        return view('guru_bk.dashboard', compact(
            'semester',
            'guruBkKelas',
            'ringkasan',
            'ringkasanPerKelas',
            'hasilPerKelas',
            'kelasBelumGenerate',
            'kelasStale',
        ));
    }
}