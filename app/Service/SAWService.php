<?php

namespace App\Service;

use App\Models\Absensi;
use App\Models\EarlyWarningResult;
use App\Models\HasilUjian;
use App\Models\Kelas;
use App\Models\NilaiTugas;
use App\Models\PerilakuSiswa;
use App\Models\Semester;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class SAWService
{
    // =========================================================================
    //  PUBLIC — dipanggil oleh Scheduler, bukan Controller
    // =========================================================================

    /**
     * Hitung SAW untuk semua kelas aktif di semester aktif.
     * Dipanggil oleh Scheduler setiap hari.
     */
    public function generateHarian(): void
    {
        $semester = $this->getSemesterAktif();
        $tanggalHitung = Carbon::today()->toDateString();

        $kelasList = Kelas::whereHas('siswas')->get();

        foreach ($kelasList as $kelas) {
            try {
                $this->generateUntukKelas($kelas->id, $semester, $tanggalHitung);

                Log::info("[SAW] Kelas {$kelas->nama_kelas} berhasil dihitung", [
                    'tanggal' => $tanggalHitung,
                    'semester' => $semester->nama,
                ]);
            } catch (\Throwable $e) {
                Log::error("[SAW] Gagal hitung kelas {$kelas->nama_kelas}: {$e->getMessage()}");
            }
        }
    }

    /**
     * Hitung SAW untuk satu kelas.
     * Bisa dipanggil dari Scheduler maupun manual jika diperlukan.
     */
    public function generateUntukKelas(
        int $kelasId,
        ?Semester $semester = null,
        ?string $tanggalHitung = null
    ): array {
        $semester ??= $this->getSemesterAktif();
        $tanggalHitung ??= Carbon::today()->toDateString();

        $siswas = Siswa::where('kelas_id', $kelasId)
            ->select('id', 'nama', 'nis', 'kelas_id')
            ->get();

        if ($siswas->isEmpty()) {
            throw new \RuntimeException("Tidak ada siswa di kelas ID {$kelasId}.");
        }

        $nilaiMentah = $siswas->map(
            fn($siswa) => $this->hitungNilaiMentah($siswa, $semester)
        );

        $hasil = $this->prosesSaw($nilaiMentah);

        $this->simpanHasil($hasil, $semester, $kelasId, $tanggalHitung);

        return [
            'semester' => $semester,
            'tanggal' => $tanggalHitung,
            'jumlah_siswa' => $siswas->count(),
            'hasil' => $hasil,
        ];
    }

    // =========================================================================
    //  HITUNG NILAI MENTAH PER SISWA
    // =========================================================================

    private function hitungNilaiMentah(Siswa $siswa, Semester $semester): array
    {
        $akademik = $this->hitungAkademik($siswa->id, $semester);
        $absensi = $this->hitungAbsensi($siswa->id, $semester);
        $perilaku = $this->hitungPerilaku($siswa->id, $semester);

        $dataTidakLengkap = !$akademik['ada_data']
            || !$absensi['ada_data']
            || !$perilaku['ada_data'];

        return [
            'siswa_id' => $siswa->id,
            'nama' => $siswa->nama,
            'nis' => $siswa->nis,
            'kelas_id' => $siswa->kelas_id,
            'c1_akademik' => $akademik['nilai'],
            'c2_absensi' => $absensi['nilai'],
            'c3_perilaku' => $perilaku['skor'],
            'total_perilaku_negatif' => $perilaku['total_negatif'],
            'total_perilaku_positif' => $perilaku['total_positif'],
            'data_tidak_lengkap' => $dataTidakLengkap,
        ];
    }

    // =========================================================================
    //  C1 — AKADEMIK
    //  Rata-rata nilai tugas dan ujian dalam semester berjalan.
    //  Jika hanya salah satu ada, pakai yang ada saja.
    // =========================================================================

    private function hitungAkademik(int $siswaId, Semester $semester): array
    {
        $rataRataTugas = NilaiTugas::where('siswa_id', $siswaId)
            ->whereNotNull('nilai')
            ->whereHas('tugas', fn($q) => $q->whereBetween('tanggal_tugas', [
                $semester->tanggal_mulai,
                $semester->tanggal_selesai,
            ]))
            ->avg('nilai');

        $rataRataUjian = HasilUjian::where('siswa_id', $siswaId)
            ->whereNotNull('nilai')
            ->whereHas('ujianHarian', fn($q) => $q->whereBetween('tanggal_ujian', [
                $semester->tanggal_mulai,
                $semester->tanggal_selesai,
            ]))
            ->avg('nilai');

        $adaData = !is_null($rataRataTugas) || !is_null($rataRataUjian);

        if (!$adaData) {
            return ['nilai' => 0.0, 'ada_data' => false];
        }

        // Kalau hanya salah satu ada, pakai yang ada
        if (is_null($rataRataTugas)) {
            return ['nilai' => round((float) $rataRataUjian, 2), 'ada_data' => true];
        }

        if (is_null($rataRataUjian)) {
            return ['nilai' => round((float) $rataRataTugas, 2), 'ada_data' => true];
        }

        return [
            'nilai' => round(((float) $rataRataTugas + (float) $rataRataUjian) / 2, 2),
            'ada_data' => true,
        ];
    }

    // =========================================================================
    //  C2 — ABSENSI MAPEL
    //  Dihitung dari % kehadiran seluruh sesi mapel dalam semester berjalan.
    //  Skala: 0 - 100 (persen kehadiran).
    //
    //  Threshold dari dokumen kurikulum SMAN 1 Cikarang Selatan:
    //  >= 90% → aman, < 90% → mulai bermasalah (lihat config ews.absensi)
    // =========================================================================

    private function hitungAbsensi(int $siswaId, Semester $semester): array
    {
        $records = Absensi::where('siswa_id', $siswaId)
            ->where('tipe', Absensi::TIPE_MAPEL)
            ->whereBetween('tanggal', [
                $semester->tanggal_mulai,
                $semester->tanggal_selesai,
            ])
            ->whereNotNull('status')
            ->get(['status']);

        if ($records->isEmpty()) {
            return ['nilai' => 0.0, 'ada_data' => false];
        }

        $total = $records->count();
        $hadir = $records->whereIn('status', ['hadir', 'terlambat'])->count();
        // Catatan: terlambat tetap dihitung hadir untuk % kehadiran,
        // karena pelanggaran terlambat sudah masuk ke indikator Perilaku.

        $persen = round(($hadir / $total) * 100, 2);

        return [
            'nilai' => $persen, // 0 - 100
            'ada_data' => true,
        ];
    }

    // =========================================================================
    //  C3 — PERILAKU
    //  Skor = max(0, 100 - total_poin_negatif)
    //  Poin positif disimpan untuk konteks AI tapi tidak menambah skor perilaku.
    //  Guru BK yang menentukan jenis dan bobot poin perilaku di tabel perilakus.
    // =========================================================================

    private function hitungPerilaku(int $siswaId, Semester $semester): array
    {
        $records = PerilakuSiswa::where('siswa_id', $siswaId)
            ->whereBetween('tanggal', [
                $semester->tanggal_mulai,
                $semester->tanggal_selesai,
            ])
            ->whereHas('perilaku', fn($q) => $q->where('status_aktif', true))
            ->with('perilaku:id,jenis,poin')
            ->get();

        if ($records->isEmpty()) {
            return [
                'skor' => 100.0,
                'total_negatif' => 0.0,
                'total_positif' => 0.0,
                'ada_data' => false,
            ];
        }

        $totalNegatif = $records
            ->filter(fn($r) => $r->perilaku->jenis === 'negatif')
            ->sum(fn($r) => abs($r->perilaku->poin));

        $totalPositif = $records
            ->filter(fn($r) => $r->perilaku->jenis === 'positif')
            ->sum(fn($r) => $r->perilaku->poin);

        return [
            'skor' => round(max(0.0, 100.0 - $totalNegatif), 2),
            'total_negatif' => round((float) $totalNegatif, 2),
            'total_positif' => round((float) $totalPositif, 2),
            'ada_data' => true,
        ];
    }

    // =========================================================================
    //  PROSES SAW — NORMALISASI + BOBOT
    //  Bobot dibaca dari config/ews.php — tidak hardcode di sini.
    //  Normalisasi: r = nilai / max (benefit criteria, semua kriteria benefit).
    // =========================================================================

    private function prosesSaw(Collection $nilaiMentah): Collection
    {
        $bobot = config('ews.bobot');

        // Max tiap kriteria untuk normalisasi
        // Jika semua 0 pakai 1 agar tidak divide by zero
        $maxC1 = $nilaiMentah->max('c1_akademik') ?: 1;
        $maxC2 = $nilaiMentah->max('c2_absensi') ?: 1;
        $maxC3 = $nilaiMentah->max('c3_perilaku') ?: 1;

        return $nilaiMentah->map(function ($row) use ($maxC1, $maxC2, $maxC3, $bobot) {

            $r1 = round($row['c1_akademik'] / $maxC1, 4);
            $r2 = round($row['c2_absensi'] / $maxC2, 4);
            $r3 = round($row['c3_perilaku'] / $maxC3, 4);

            $vi = round(
                ($r1 * $bobot['c1_akademik'])
                + ($r2 * $bobot['c2_absensi'])
                + ($r3 * $bobot['c3_perilaku']),
                4
            );

            return [
                'siswa_id' => $row['siswa_id'],
                'nama' => $row['nama'],
                'nis' => $row['nis'],
                'kelas_id' => $row['kelas_id'],

                // Nilai mentah
                'c1_akademik' => $row['c1_akademik'],
                'c2_absensi' => $row['c2_absensi'],
                'c3_perilaku' => $row['c3_perilaku'],
                'total_perilaku_negatif' => $row['total_perilaku_negatif'],
                'total_perilaku_positif' => $row['total_perilaku_positif'],

                // Matriks normalisasi
                'r1_akademik' => $r1,
                'r2_absensi' => $r2,
                'r3_perilaku' => $r3,

                // Hasil SAW
                'skor_akhir' => $vi,
                'kategori' => $this->tentukanKategori($vi),
                'data_tidak_lengkap' => $row['data_tidak_lengkap'],
            ];

        })->sortBy('skor_akhir')->values();
    }

    // =========================================================================
    //  SIMPAN HASIL — SNAPSHOT HARIAN
    //  Upsert by siswa_id + semester_id + tanggal_hitung
    //  Satu siswa bisa punya banyak record (satu per hari) → untuk tracking tren.
    // =========================================================================

    private function simpanHasil(
        Collection $hasil,
        Semester $semester,
        int $kelasId,
        string $tanggalHitung
    ): void {
        $generatedAt = now();

        $rows = $hasil->map(fn($item) => [
            'siswa_id' => $item['siswa_id'],
            'semester_id' => $semester->id,
            'kelas_id' => $kelasId,
            'tanggal_hitung' => $tanggalHitung,
            'generated_at' => $generatedAt,
            'c1_akademik' => $item['c1_akademik'],
            'c2_absensi' => $item['c2_absensi'],
            'c3_perilaku' => $item['c3_perilaku'],
            'total_perilaku_negatif' => $item['total_perilaku_negatif'],
            'total_perilaku_positif' => $item['total_perilaku_positif'],
            'r1_akademik' => $item['r1_akademik'],
            'r2_absensi' => $item['r2_absensi'],
            'r3_perilaku' => $item['r3_perilaku'],
            'skor_akhir' => $item['skor_akhir'],
            'kategori' => $item['kategori'],
            'data_tidak_lengkap' => $item['data_tidak_lengkap'],
            'created_at' => $generatedAt,
            'updated_at' => $generatedAt,
        ])->values()->toArray();

        EarlyWarningResult::upsert(
            $rows,
            uniqueBy: ['siswa_id', 'semester_id', 'tanggal_hitung'],
            update: [
                'kelas_id',
                'generated_at',
                'c1_akademik',
                'c2_absensi',
                'c3_perilaku',
                'total_perilaku_negatif',
                'total_perilaku_positif',
                'r1_akademik',
                'r2_absensi',
                'r3_perilaku',
                'skor_akhir',
                'kategori',
                'data_tidak_lengkap',
                'updated_at',
            ]
        );
    }

    // =========================================================================
    //  HELPER
    // =========================================================================

    private function getSemesterAktif(): Semester
    {
        $semester = Semester::where('is_active', true)->first();

        if (!$semester) {
            throw new \RuntimeException(
                'Tidak ada semester aktif. Aktifkan semester terlebih dahulu.'
            );
        }

        return $semester;
    }

    private function tentukanKategori(float $skor): string
    {
        $threshold = config('ews.threshold');

        if ($skor >= $threshold['aman']) {
            return 'aman';
        }

        if ($skor >= $threshold['perhatian']) {
            return 'perhatian';
        }

        return 'binaan';
    }
}