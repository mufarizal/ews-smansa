<?php

namespace App\Http\Controllers\WaliKelas;

use App\Exports\WaliKelasSiswaExport;
use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\HasilUjian;
use App\Models\NilaiTugas;
use App\Models\PerilakuSiswa;
use App\Models\Semester;
use App\Service\EwsSnapshotService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class KelasSayaController extends Controller
{
    public function __construct(private EwsSnapshotService $snapshotService) {}

    public function index()
    {
        $guru = Auth::user()->guru;

        if (! $guru) {
            return view('wali_kelas.kelas_saya.index', [
                'kelas' => null,
                'semester' => null,
                'siswa' => collect(),
                'siswaCount' => 0,
                'ringkasan' => $this->emptyRingkasan(),
                'activeTab' => request('tab', 'ringkasan'),
            ]);
        }

        $kelas = $guru->kelasDiampu()
            ->with(['semester'])
            ->withCount(['siswas as siswa_count'])
            ->orderBy('nama_kelas')
            ->first();

        if (! $kelas) {
            return view('wali_kelas.kelas_saya.index', [
                'kelas' => null,
                'semester' => null,
                'siswa' => collect(),
                'siswaCount' => 0,
                'ringkasan' => $this->emptyRingkasan(),
                'activeTab' => request('tab', 'ringkasan'),
            ]);
        }

        $semester = Semester::where('is_active', true)->firstOrFail();

        // Roster alfabetis (BUKAN diurut prioritas kategori SAW) — struktur existing
        // dipertahankan, hanya filter absensi dibenahi jadi tipe='mapel' konsisten
        // dengan definisi SAW (sebelumnya tidak difilter tipe sama sekali).
        $siswa = $kelas->siswas()
            ->with(['kelas', 'user'])
            ->withAvg('nilaiTugas as nilai_tugas_avg', 'nilai')
            ->withAvg('hasilUjians as nilai_ujian_avg', 'nilai')
            ->withCount([
                'absensis as total_absensi_count' => fn ($q) => $q->where('tipe', Absensi::TIPE_MAPEL),
                'absensis as hadir_absensi_count' => fn ($q) => $q->where('tipe', Absensi::TIPE_MAPEL)->where('status', 'hadir'),
                'absensis as alpha_absensi_count' => fn ($q) => $q->where('tipe', Absensi::TIPE_MAPEL)->where('status', 'alpha'),
                'absensis as terlambat_absensi_count' => fn ($q) => $q->where('tipe', Absensi::TIPE_MAPEL)->where('status', 'terlambat'),
                'perilakuSiswas as catatan_perilaku_count',
            ])
            ->withSum(['absensis as total_keterlambatan_menit' => fn ($q) => $q->where('tipe', Absensi::TIPE_MAPEL)->where('status', 'terlambat')], 'terlambat_menit')
            ->orderBy('nama')
            ->orderBy('nis')
            ->paginate(20)
            ->withQueryString();

        // Snapshot SAW terbaru kelas ini, key by siswa_id, untuk di-merge ke roster
        // (append attribute ke collection yang sudah ada, bukan collection terpisah).
        $snapshotPerSiswa = $this->snapshotService
            ->latestSnapshotPerKelas($kelas->id, $semester->id)
            ->keyBy('siswa_id');

        $tanggalTerbaru = $snapshotPerSiswa->first()->tanggal_hitung ?? null;
        $siswaIdsWithSnapshot = $snapshotPerSiswa->pluck('siswa_id')->filter()->values()->toArray();
        $trendsBatch = $tanggalTerbaru
            ? $this->snapshotService->trendHarianBatch($siswaIdsWithSnapshot, $semester->id, $tanggalTerbaru)
            : collect();

        $rekomendasiCache = $snapshotPerSiswa
            ->filter(fn ($s) => ! empty($s->kategori))
            ->unique(fn ($s) => $s->kelas_id.'|'.$s->kategori)
            ->mapWithKeys(fn ($s) => [
                $s->kelas_id.'|'.$s->kategori => $this->snapshotService->aiRekomendasiSiswa($s),
            ]);

        $siswa->through(function ($item) use ($snapshotPerSiswa, $trendsBatch, $rekomendasiCache) {
            $hasilSaw = $snapshotPerSiswa->get($item->id);

            $item->saw_kategori = $hasilSaw->kategori ?? null;
            $item->saw_skor_akhir = $hasilSaw->skor_akhir ?? null;
            $item->saw_data_tidak_lengkap = $hasilSaw->data_tidak_lengkap ?? null;

            $item->saw_trend_harian = $hasilSaw
                ? ($trendsBatch->get($item->id) ?? ['arah' => 'tetap', 'selisih' => 0.0, 'skor_sebelumnya' => null])
                : null;

            $item->saw_ai_rekomendasi = $hasilSaw && ! empty($hasilSaw->kategori)
                ? ($rekomendasiCache->get($hasilSaw->kelas_id.'|'.$hasilSaw->kategori) ?? null)
                : null;

            return $item;
        });

        $siswaCount = $kelas->siswa_count;

        $activeTab = request('tab', 'ringkasan');

        $ringkasan = $this->calculateRingkasan($kelas);

        return view('wali_kelas.kelas_saya.index', compact(
            'kelas',
            'semester',
            'siswa',
            'siswaCount',
            'ringkasan',
            'activeTab'
        ));
    }

    public function export()
    {
        $guru = Auth::user()->guru;

        if (! $guru) {
            abort(403, 'Data guru tidak ditemukan.');
        }

        $kelas = $guru->kelasDiampu()
            ->with(['semester'])
            ->orderBy('nama_kelas')
            ->first();

        if (! $kelas) {
            abort(403, 'Anda belum ditetapkan sebagai wali kelas.');
        }

        return Excel::download(
            new WaliKelasSiswaExport($kelas->id),
            'Data-Siswa-'.$kelas->nama_kelas.'-'.Carbon::now()->format('Ymd-His').'.xlsx'
        );
    }

    private function emptyRingkasan(): object
    {
        return (object) [
            'total_siswa' => 0,
            'hadir_harian' => 0,
            'total_harian' => 0,
            'hadir_mapel' => 0,
            'total_mapel' => 0,
            'rata_rata_tugas' => 0,
            'rata_rata_ujian' => 0,
            'total_keterlambatan_menit' => 0,
            'total_catatan_perilaku' => 0,
        ];
    }

    private function calculateRingkasan($kelas): object
    {
        $siswaIds = $kelas->siswas()->pluck('id');

        $totalSiswa = $siswaIds->count();

        $totalHadirHarian = Absensi::whereIn('siswa_id', $siswaIds)
            ->where('tipe', 'harian')
            ->where('status', 'hadir')
            ->count();

        $totalHarian = Absensi::whereIn('siswa_id', $siswaIds)
            ->where('tipe', 'harian')
            ->count();

        $totalHadirMapel = Absensi::whereIn('siswa_id', $siswaIds)
            ->where('tipe', 'mapel')
            ->where('status', 'hadir')
            ->count();

        $totalMapel = Absensi::whereIn('siswa_id', $siswaIds)
            ->where('tipe', 'mapel')
            ->count();

        $rataRataTugas = NilaiTugas::whereIn('siswa_id', $siswaIds)
            ->whereNotNull('nilai')
            ->avg('nilai');

        $rataRataTugas = $rataRataTugas ? round($rataRataTugas, 1) : 0;

        $rataRataUjian = HasilUjian::whereIn('siswa_id', $siswaIds)
            ->whereNotNull('nilai')
            ->avg('nilai');

        $rataRataUjian = $rataRataUjian ? round($rataRataUjian, 1) : 0;

        $totalKeterlambatan = Absensi::whereIn('siswa_id', $siswaIds)
            ->where('status', 'terlambat')
            ->sum('terlambat_menit');

        $totalCatatanPerilaku = PerilakuSiswa::whereIn('siswa_id', $siswaIds)->count();

        return (object) [
            'total_siswa' => $totalSiswa,
            'hadir_harian' => $totalHadirHarian,
            'total_harian' => $totalHarian,
            'hadir_mapel' => $totalHadirMapel,
            'total_mapel' => $totalMapel,
            'rata_rata_tugas' => $rataRataTugas,
            'rata_rata_ujian' => $rataRataUjian,
            'total_keterlambatan_menit' => (int) $totalKeterlambatan,
            'total_catatan_perilaku' => $totalCatatanPerilaku,
        ];
    }
}
