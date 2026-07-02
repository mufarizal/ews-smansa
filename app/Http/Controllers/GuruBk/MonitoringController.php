<?php

namespace App\Http\Controllers\GuruBk;

use App\Http\Controllers\Controller;
use App\Models\GuruBkKelas;
use App\Models\Kelas;
use App\Models\Semester;
use App\Models\Siswa;
use App\Service\EwsSnapshotService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MonitoringController extends Controller
{
    public function __construct(private EwsSnapshotService $snapshotService)
    {
    }

    private function getGuruId(): int
    {
        return Auth::user()->guru->id;
    }

    private function kelasDiampu(int $guruId, int $semesterId)
    {
        return GuruBkKelas::with('kelas')
            ->where('guru_id', $guruId)
            ->where('semester_id', $semesterId)
            ->get();
    }

    private function pastikanKelasDiampu(int $kelasId, int $guruId, int $semesterId): void
    {
        $diampu = GuruBkKelas::where('guru_id', $guruId)
            ->where('semester_id', $semesterId)
            ->where('kelas_id', $kelasId)
            ->exists();

        abort_unless($diampu, 403);
    }

    public function index()
    {
        $semester = Semester::where('is_active', true)->firstOrFail();
        $guruId = $this->getGuruId();

        $guruBkKelas = $this->kelasDiampu($guruId, $semester->id);

        if ($guruBkKelas->count() === 1) {
            return redirect()->route('guru_bk.monitoring.show', $guruBkKelas->first()->kelas_id);
        }

        $ringkasanPerKelas = $guruBkKelas->map(function ($gbk) use ($semester) {
            $snapshot = $this->snapshotService->latestSnapshotPerKelas($gbk->kelas_id, $semester->id);

            return [
                'kelas' => $gbk->kelas,
                'binaan' => $snapshot->where('kategori', 'binaan')->count(),
                'perhatian' => $snapshot->where('kategori', 'perhatian')->count(),
                'aman' => $snapshot->where('kategori', 'aman')->count(),
                'total' => $snapshot->count(),
            ];
        });

        return view('guru_bk.monitoring.index', compact('semester', 'ringkasanPerKelas'));
    }

    public function show(Kelas $kelas, Request $request)
    {
        $semester = Semester::where('is_active', true)->firstOrFail();
        $guruId = $this->getGuruId();

        $this->pastikanKelasDiampu($kelas->id, $guruId, $semester->id);

        $snapshot = $this->snapshotService->latestSnapshotPerKelas($kelas->id, $semester->id);

        $kategoriFilter = $request->query('kategori');

        if ($kategoriFilter && in_array($kategoriFilter, ['binaan', 'perhatian', 'aman'], true)) {
            $snapshot = $snapshot->where('kategori', $kategoriFilter)->values();
        }

        $siswaTerurut = $this->snapshotService->urutkanPrioritas($snapshot)
            ->map(function ($item) use ($semester) {
                $item->trend_harian = $this->snapshotService->trendHarian(
                    $item->siswa_id,
                    $semester->id,
                    $item->tanggal_hitung
                );
                return $item;
            });

        $trendMingguanKelas = $this->snapshotService->trendMingguan($kelas->id, $semester->id);

        return view('guru_bk.monitoring.show', compact(
            'kelas',
            'semester',
            'siswaTerurut',
            'trendMingguanKelas',
            'kategoriFilter'
        ));
    }

    /**
     * Detail 1 siswa: breakdown skor, riwayat SAW dengan filter range,
     * rekomendasi AI terbaru (decode by NIS).
     */
    public function showSiswa(Kelas $kelas, Siswa $siswa, Request $request)
    {
        $semester = Semester::where('is_active', true)->firstOrFail();
        $guruId = $this->getGuruId();

        $this->pastikanKelasDiampu($kelas->id, $guruId, $semester->id);
        abort_unless($siswa->kelas_id === $kelas->id, 404);

        $hasilTerbaru = $this->snapshotService->latestSnapshotPerSiswa($siswa->id, $semester->id);

        $trendHarian = $hasilTerbaru
            ? $this->snapshotService->trendHarian($siswa->id, $semester->id, $hasilTerbaru->tanggal_hitung)
            : null;

        $rekomendasiAi = $hasilTerbaru
            ? $this->snapshotService->aiRekomendasiSiswa($hasilTerbaru)
            : null;

        [$dari, $sampai] = $this->resolveRangeTanggal($request);

        $riwayat = $this->snapshotService->riwayatSiswa($siswa->id, $semester->id, $dari, $sampai);

        return view('guru_bk.monitoring.siswa', compact(
            'kelas',
            'siswa',
            'semester',
            'hasilTerbaru',
            'trendHarian',
            'rekomendasiAi',
            'riwayat',
            'dari',
            'sampai'
        ));
    }

    private function resolveRangeTanggal(Request $request): array
    {
        $customDari = $request->query('dari');
        $customSampai = $request->query('sampai');

        if ($customDari && $customSampai) {
            return [$customDari, $customSampai];
        }

        $range = (int) $request->query('range', 30);

        if (!in_array($range, [7, 30, 90, 180, 365], true)) {
            $range = 30;
        }

        $sampai = Carbon::today()->toDateString();
        $dari = Carbon::today()->subDays($range - 1)->toDateString();

        return [$dari, $sampai];
    }
}