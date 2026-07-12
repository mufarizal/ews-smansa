<?php

namespace App\Http\Controllers\GuruBk;

use App\Exports\MonitoringSiswaExport;
use App\Http\Controllers\Controller;
use App\Models\AiRecommendation;
use App\Models\EarlyWarningResult;
use App\Models\GuruBkKelas;
use App\Models\Kelas;
use App\Models\Semester;
use App\Models\Siswa;
use App\Service\EwsSnapshotService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class MonitoringController extends Controller
{
    public function __construct(private EwsSnapshotService $snapshotService) {}

    private function getGuruId(): int
    {
        $guru = Auth::user()->guru;
        abort_if(! $guru, 403, 'Data guru tidak ditemukan.');

        return $guru->id;
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
        $siswaKelas = Siswa::select('id', 'kelas_id', 'nis', 'nama')
            ->where('kelas_id', $kelas->id)
            ->orderBy('nama')
            ->get();
        $snapshotBySiswaId = $snapshot->keyBy('siswa_id');

        $kategoriFilter = $request->query('kategori');

        $siswaIdsWithSnapshot = $snapshot->pluck('siswa_id')->filter()->values()->toArray();
        $tanggalTerbaru = $snapshot->first()->tanggal_hitung ?? null;
        $trendsBatch = $tanggalTerbaru
            ? $this->snapshotService->trendHarianBatch($siswaIdsWithSnapshot, $semester->id, $tanggalTerbaru)
            : collect();

        $siswaTerurut = $siswaKelas
            ->map(function ($siswa) use ($snapshotBySiswaId, $trendsBatch) {
                $item = $snapshotBySiswaId->get($siswa->id);

                if (! $item) {
                    return (object) [
                        'siswa_id' => $siswa->id,
                        'siswa' => $siswa,
                        'kategori' => null,
                        'skor_akhir' => null,
                        'data_tidak_lengkap' => true,
                        'trend_harian' => null,
                        'snapshot_tidak_ada' => true,
                    ];
                }

                $item->trend_harian = $trendsBatch->get($item->siswa_id, ['arah' => 'tetap', 'selisih' => 0.0, 'skor_sebelumnya' => null]);
                $item->snapshot_tidak_ada = false;

                return $item;
            })
            ->when($kategoriFilter && in_array($kategoriFilter, EarlyWarningResult::KATEGORI, true), function ($collection) use ($kategoriFilter) {
                return $collection->filter(fn ($item) => ($item->kategori ?? null) === $kategoriFilter)->values();
            })
            ->sortBy(function ($item) {
                if (! empty($item->snapshot_tidak_ada)) {
                    return sprintf('99-%s', strtolower($item->siswa->nama ?? ''));
                }

                $rankMap = EarlyWarningResult::URUTAN_KATEGORI;
                $rank = $rankMap[$item->kategori] ?? 99;

                return sprintf('%02d-%015.4f-%s', $rank, (float) ($item->skor_akhir ?? 0), strtolower($item->siswa->nama ?? ''));
            })
            ->values();

        $trendMingguanKelas = $this->snapshotService->trendMingguan($kelas->id, $semester->id);

        return view('guru_bk.monitoring.show', compact(
            'kelas',
            'semester',
            'siswaTerurut',
            'trendMingguanKelas',
            'kategoriFilter'
        ));
    }

    public function export(Kelas $kelas, Request $request)
    {
        $semester = Semester::where('is_active', true)->firstOrFail();
        $guruId = $this->getGuruId();

        $this->pastikanKelasDiampu($kelas->id, $guruId, $semester->id);

        $kategoriFilter = $request->query('kategori');

        return Excel::download(
            new MonitoringSiswaExport($kelas->id, $kategoriFilter),
            'Monitoring-SAW-'.$kelas->nama_kelas.'-'.Carbon::now()->format('Ymd-His').'.xlsx'
        );
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

        abort_unless(
            (int) $siswa->kelas_id === (int) $kelas->id,
            404
        );

        $hasilTerbaru = $this->snapshotService->latestSnapshotPerSiswa(
            $siswa->id,
            $semester->id
        );

        $hasilTerbaru?->loadMissing('siswa');

        $trendHarian = $hasilTerbaru
            ? $this->snapshotService->trendHarian(
                $siswa->id,
                $semester->id,
                $hasilTerbaru->tanggal_hitung
            )
            : null;

        $rekomendasiAi = $hasilTerbaru
            ? $this->snapshotService->aiRekomendasiSiswa($hasilTerbaru)
            : null;

        $aiFilterId = $request->query('ai_filter_id');
        if ($aiFilterId && $hasilTerbaru) {
            $rekomendasiRecord = AiRecommendation::where('id', $aiFilterId)
                ->where('scope', 'kelas')
                ->where('scope_id', $hasilTerbaru->kelas_id)
                ->where('kategori', $hasilTerbaru->kategori)
                ->where('semester_id', $semester->id)
                ->first();

            if ($rekomendasiRecord) {
                $daftar = is_array($rekomendasiRecord->rekomendasi)
                    ? $rekomendasiRecord->rekomendasi
                    : json_decode((string) $rekomendasiRecord->rekomendasi, true);

                foreach ($daftar ?? [] as $entry) {
                    if (($entry['nis'] ?? null) == $siswa->nis) {
                        $rekomendasiAi = [
                            'penyebab' => $entry['penyebab'] ?? [],
                            'saran' => $entry['saran'] ?? [],
                            'provider_used' => $rekomendasiRecord->provider_used ?? null,
                            'generated_at' => $rekomendasiRecord->generated_at,
                        ];
                        break;
                    }
                }
            }
        }

        $aiRekomendasiHistory = $hasilTerbaru
            ? AiRecommendation::untukKelasSekarang($hasilTerbaru->kelas_id)
                ->filter(fn ($r) => $r->kategori === ($hasilTerbaru->kategori ?? null))
                ->values()
            : collect();

        [$dari, $sampai] = $this->resolveRangeTanggal($request);

        $riwayat = $this->snapshotService->riwayatSiswa(
            $siswa->id,
            $semester->id,
            $dari,
            $sampai
        );

        return view('guru_bk.monitoring.siswa', compact(
            'kelas',
            'siswa',
            'semester',
            'hasilTerbaru',
            'trendHarian',
            'rekomendasiAi',
            'aiFilterId',
            'aiRekomendasiHistory',
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
            try {
                $dari = Carbon::parse($customDari)->toDateString();
                $sampai = Carbon::parse($customSampai)->toDateString();

                if ($dari > $sampai) {
                    [$dari, $sampai] = [$sampai, $dari];
                }

                return [$dari, $sampai];
            } catch (\Throwable $e) {
                // Fall back to predefined range when invalid custom date is provided.
            }
        }

        $range = (int) $request->query('range', 30);

        if (! in_array($range, [7, 30, 90, 180, 365], true)) {
            $range = 30;
        }

        $sampai = Carbon::today()->toDateString();
        $dari = Carbon::today()->subDays($range - 1)->toDateString();

        return [$dari, $sampai];
    }
}
