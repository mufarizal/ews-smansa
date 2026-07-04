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
        $guru = Auth::user()->guru;
        abort_if(!$guru, 403, 'Data guru tidak ditemukan.');

        return $guru->id;
    }

    public function index()
    {
        $semester = Semester::where('is_active', true)->firstOrFail();
        $guruId = $this->getGuruId();

        $guruBkKelas = GuruBkKelas::with('kelas')
            ->where('guru_id', $guruId)
            ->where('semester_id', $semester->id)
            ->get();

        $kelasIds = $guruBkKelas->pluck('kelas_id');

        $semuaHasilTerbaru = $this->snapshotService->latestSnapshotPerKelasBatch(
            $kelasIds->toArray(),
            $semester->id
        );

        $ringkasan = [
            'binaan' => $semuaHasilTerbaru->where('kategori', 'binaan')->count(),
            'perhatian' => $semuaHasilTerbaru->where('kategori', 'perhatian')->count(),
            'aman' => $semuaHasilTerbaru->where('kategori', 'aman')->count(),
            'total' => $semuaHasilTerbaru->count(),
        ];

        $snapshotPerKelas = $semuaHasilTerbaru->groupBy('kelas_id');

        $ringkasanPerKelas = $guruBkKelas->mapWithKeys(function ($gbk) use ($semester, $snapshotPerKelas) {
            $snapshot = $snapshotPerKelas->get($gbk->kelas_id, collect());

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
                ->take(5);

            $trends = $this->snapshotService->trendHarianBatch(
                $siswaTerburuk->pluck('siswa_id')->toArray(),
                $semester->id,
                $siswaTerburuk->first()->tanggal_hitung
            );

            $siswaTerburuk = $siswaTerburuk->map(function ($item) use ($trends) {
                $item->trend_harian = $trends->get($item->siswa_id, ['arah' => 'tetap', 'selisih' => 0.0, 'skor_sebelumnya' => null]);
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

        $hasilPerKelas = EarlyWarningResult::where('semester_id', $semester->id)
            ->whereIn('kelas_id', $kelasIds)
            ->selectRaw('kelas_id, COUNT(*) as total_siswa, MAX(generated_at) as last_generated_at')
            ->groupBy('kelas_id')
            ->get()
            ->keyBy('kelas_id');

        $kelasBelumGenerate = $guruBkKelas->filter(
            fn($gbk) => !isset($hasilPerKelas[$gbk->kelas_id])
        );

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