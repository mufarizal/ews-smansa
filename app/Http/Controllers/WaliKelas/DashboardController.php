<?php

namespace App\Http\Controllers\WaliKelas;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\Semester;
use App\Service\EwsSnapshotService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(private EwsSnapshotService $snapshotService) {}

    public function index()
    {
        $guru = Auth::user()->guru;

        if (! $guru) {
            return view('wali_kelas.dashboard', [
                'kelas' => null,
                'semester' => null,
                'ringkasan' => $this->emptyRingkasan(),
                'siswaTerurut' => collect(),
                'trendMingguanKelas' => null,
                'jadwals' => collect(),
                'todayHari' => null,
            ]);
        }

        $kelas = $guru->kelasDiampu()
            ->with(['semester'])
            ->first();

        $semester = Semester::where('is_active', true)->firstOrFail();

        $ringkasan = $this->emptyRingkasan();
        $siswaTerurut = collect();
        $trendMingguanKelas = null;

        if ($kelas) {
            $snapshot = $this->snapshotService->latestSnapshotPerKelas($kelas->id, $semester->id);

            $ringkasan = [
                'binaan' => $snapshot->where('kategori', 'binaan')->count(),
                'perhatian' => $snapshot->where('kategori', 'perhatian')->count(),
                'aman' => $snapshot->where('kategori', 'aman')->count(),
                'total' => $snapshot->count(),
            ];

            $siswaTerurut = $this->snapshotService->urutkanPrioritas($snapshot);

            $tanggalTerbaru = $snapshot->first()->tanggal_hitung ?? null;
            $trendsBatch = $tanggalTerbaru
                ? $this->snapshotService->trendHarianBatch(
                    $siswaTerurut->pluck('siswa_id')->filter()->values()->toArray(),
                    $semester->id,
                    $tanggalTerbaru
                )
                : collect();

            $siswaTerurut = $siswaTerurut->map(function ($item) use ($trendsBatch) {
                $item->trend_harian = $trendsBatch->get($item->siswa_id, ['arah' => 'tetap', 'selisih' => 0.0, 'skor_sebelumnya' => null]);

                return $item;
            });

            $trendMingguanKelas = $this->snapshotService->trendMingguan($kelas->id, $semester->id);
        }

        $todayHari = Jadwal::carbonToHari(now());
        $jadwals = Jadwal::with(['mapel', 'kelas'])
            ->whereHas('kelas', function ($q) use ($kelas) {
                $q->where('id', $kelas?->id);
            })
            ->where('hari', $todayHari)
            ->orderBy('jam_mulai')
            ->get();

        return view('wali_kelas.dashboard', compact(
            'kelas',
            'semester',
            'ringkasan',
            'siswaTerurut',
            'trendMingguanKelas',
            'jadwals',
            'todayHari'
        ));
    }

    private function emptyRingkasan(): array
    {
        return [
            'binaan' => 0,
            'perhatian' => 0,
            'aman' => 0,
            'total' => 0,
        ];
    }
}
