<?php

namespace App\Service;

use App\Models\AiRecommendation;
use App\Models\EarlyWarningResult;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EwsSnapshotService
{
    /**
     * Ambil snapshot EarlyWarningResult terbaru (tanggal_hitung = MAX) untuk 1 kelas.
     */
    public function latestSnapshotPerKelas(int $kelasId, int $semesterId): Collection
    {
        $tanggalTerbaru = EarlyWarningResult::where('kelas_id', $kelasId)
            ->where('semester_id', $semesterId)
            ->max('tanggal_hitung');

        if (!$tanggalTerbaru) {
            return collect();
        }

        return EarlyWarningResult::with('siswa')
            ->where('kelas_id', $kelasId)
            ->where('semester_id', $semesterId)
            ->where('tanggal_hitung', $tanggalTerbaru)
            ->get();
    }

    /**
     * Ambil snapshot terbaru untuk BANYAK kelas dalam 1 query (batching).
     */
    public function latestSnapshotPerKelasBatch(array $kelasIds, int $semesterId): Collection
    {
        if (empty($kelasIds)) {
            return collect();
        }

        $latestDates = EarlyWarningResult::query()
            ->whereIn('kelas_id', $kelasIds)
            ->where('semester_id', $semesterId)
            ->select('kelas_id', DB::raw('MAX(tanggal_hitung) as tanggal_hitung'))
            ->groupBy('kelas_id')
            ->get()
            ->mapWithKeys(fn ($row) => [(int) $row->kelas_id => $row->tanggal_hitung]);

        if ($latestDates->isEmpty()) {
            return collect();
        }

        $pairs = $latestDates
            ->map(fn ($tanggal, $kelasId) => ['kelas_id' => $kelasId, 'tanggal_hitung' => $tanggal])
            ->values()
            ->all();

        return EarlyWarningResult::with('siswa')
            ->where(function ($query) use ($pairs) {
                foreach ($pairs as $pair) {
                    $query->orWhere(function ($q) use ($pair) {
                        $q->where('kelas_id', $pair['kelas_id'])
                            ->where('tanggal_hitung', $pair['tanggal_hitung']);
                    });
                }
            })
            ->get();
    }

    /**
     * Ambil snapshot EarlyWarningResult terbaru untuk 1 siswa.
     */
    public function latestSnapshotPerSiswa(int $siswaId, int $semesterId): ?EarlyWarningResult
    {
        return EarlyWarningResult::where('siswa_id', $siswaId)
            ->where('semester_id', $semesterId)
            ->orderByDesc('tanggal_hitung')
            ->first();
    }

    /**
     * Trend harian: bandingkan skor_akhir snapshot pada $tanggalTerbaru vs
     * snapshot dengan tanggal_hitung TERBESAR SEBELUM $tanggalTerbaru untuk
     * siswa yang sama. Dinamis, bukan fix H-1 kalender.
     *
     * @return array{arah: string, selisih: float, skor_sebelumnya: ?float}
     */
    public function trendHarian(int $siswaId, int $semesterId, string $tanggalTerbaru): array
    {
        $current = EarlyWarningResult::where('siswa_id', $siswaId)
            ->where('semester_id', $semesterId)
            ->where('tanggal_hitung', $tanggalTerbaru)
            ->first();

        if (!$current) {
            return ['arah' => 'baru', 'selisih' => 0.0, 'skor_sebelumnya' => null];
        }

        $sebelumnya = EarlyWarningResult::where('siswa_id', $siswaId)
            ->where('semester_id', $semesterId)
            ->where('tanggal_hitung', '<', $tanggalTerbaru)
            ->orderByDesc('tanggal_hitung')
            ->first();

        if (!$sebelumnya) {
            return ['arah' => 'baru', 'selisih' => 0.0, 'skor_sebelumnya' => null];
        }

        $selisih = round((float) $current->skor_akhir - (float) $sebelumnya->skor_akhir, 4);

        $arah = 'tetap';
        if ($selisih > 0) {
            $arah = 'naik';
        } elseif ($selisih < 0) {
            $arah = 'turun';
        }

        return [
            'arah' => $arah,
            'selisih' => $selisih,
            'skor_sebelumnya' => (float) $sebelumnya->skor_akhir,
        ];
    }

    /**
     * Batch trend harian untuk banyak siswa yang SAMA snapshot date.
     * Menggantikan N panggilan trendHarian() menjadi maksimal 2 query.
     *
     * @return Collection<int, array{arah: string, selisih: float, skor_sebelumnya: ?float}>
     */
    public function trendHarianBatch(array $siswaIds, int $semesterId, string $tanggalTerbaru): Collection
    {
        if (empty($siswaIds)) {
            return collect();
        }

        $currentRecords = EarlyWarningResult::whereIn('siswa_id', $siswaIds)
            ->where('semester_id', $semesterId)
            ->where('tanggal_hitung', $tanggalTerbaru)
            ->get()
            ->keyBy('siswa_id');

        $previousRecords = EarlyWarningResult::whereIn('siswa_id', $siswaIds)
            ->where('semester_id', $semesterId)
            ->where('tanggal_hitung', '<', $tanggalTerbaru)
            ->orderByDesc('tanggal_hitung')
            ->get()
            ->groupBy('siswa_id')
            ->map(fn ($group) => $group->first())
            ->keyBy('siswa_id');

        return $currentRecords->map(function ($current) use ($previousRecords) {
            $sebelumnya = $previousRecords->get($current->siswa_id);

            if (!$sebelumnya) {
                return ['arah' => 'baru', 'selisih' => 0.0, 'skor_sebelumnya' => null];
            }

            $selisih = round((float) $current->skor_akhir - (float) $sebelumnya->skor_akhir, 4);

            $arah = 'tetap';
            if ($selisih > 0) {
                $arah = 'naik';
            } elseif ($selisih < 0) {
                $arah = 'turun';
            }

            return [
                'arah' => $arah,
                'selisih' => $selisih,
                'skor_sebelumnya' => (float) $sebelumnya->skor_akhir,
            ];
        });
    }

    /**
     * Trend mingguan per kelas: bandingkan rata-rata skor_akhir pada snapshot
     * PERTAMA yang tersedia minggu ini (Senin-Jumat, otomatis geser kalau
     * Senin libur/tidak ada data) vs snapshot TERBARU minggu ini.
     * Robust terhadap libur — tidak butuh isFriday()/isMonday().
     *
     * @return array{arah: string, selisih: float, rata_rata_awal: ?float, rata_rata_terbaru: ?float}
     */
    public function trendMingguan(int $kelasId, int $semesterId): array
    {
        $awalMinggu = Carbon::now()->startOfWeek(Carbon::MONDAY)->toDateString();
        $akhirMinggu = Carbon::now()->endOfWeek(Carbon::FRIDAY)->toDateString();

        $baseQuery = fn() => EarlyWarningResult::where('kelas_id', $kelasId)
            ->where('semester_id', $semesterId)
            ->whereBetween('tanggal_hitung', [$awalMinggu, $akhirMinggu]);

        $tanggalAwalTersedia = $baseQuery()->min('tanggal_hitung');
        $tanggalTerbaruTersedia = $baseQuery()->max('tanggal_hitung');

        if (!$tanggalAwalTersedia || !$tanggalTerbaruTersedia) {
            return ['arah' => 'baru', 'selisih' => 0.0, 'rata_rata_awal' => null, 'rata_rata_terbaru' => null];
        }

        $rataAwal = (float) EarlyWarningResult::where('kelas_id', $kelasId)
            ->where('semester_id', $semesterId)
            ->where('tanggal_hitung', $tanggalAwalTersedia)
            ->avg('skor_akhir');

        $rataTerbaru = (float) EarlyWarningResult::where('kelas_id', $kelasId)
            ->where('semester_id', $semesterId)
            ->where('tanggal_hitung', $tanggalTerbaruTersedia)
            ->avg('skor_akhir');

        if ($tanggalAwalTersedia === $tanggalTerbaruTersedia) {
            return [
                'arah' => 'baru',
                'selisih' => 0.0,
                'rata_rata_awal' => round($rataAwal, 4),
                'rata_rata_terbaru' => round($rataTerbaru, 4),
            ];
        }

        $selisih = round($rataTerbaru - $rataAwal, 4);

        $arah = 'tetap';
        if ($selisih > 0) {
            $arah = 'naik';
        } elseif ($selisih < 0) {
            $arah = 'turun';
        }

        return [
            'arah' => $arah,
            'selisih' => $selisih,
            'rata_rata_awal' => round($rataAwal, 4),
            'rata_rata_terbaru' => round($rataTerbaru, 4),
        ];
    }

    public function urutkanPrioritas(Collection $hasil): Collection
    {
        $urutanKategori = ['binaan' => 0, 'perhatian' => 1, 'aman' => 2];

        return $hasil->sortBy(function ($item) use ($urutanKategori) {
            $rank = $urutanKategori[$item->kategori] ?? 99;

            // Komposit: rank kategori dulu, baru skor_akhir (padded supaya urut string aman)
            return sprintf('%02d-%015.4f', $rank, $item->skor_akhir);
        })->values();
    }

    public function aiRekomendasiSiswa(EarlyWarningResult $hasil): ?array
    {
        $siswa = $hasil->siswa;

        if (!$siswa || empty($siswa->nis)) {
            return null;
        }

        $rekomendasiKelas = AiRecommendation::where('scope', 'kelas')
            ->where('scope_id', $hasil->kelas_id)
            ->where('kategori', $hasil->kategori)
            ->where('semester_id', $hasil->semester_id)
            ->latest('generated_at')
            ->first();

        if (!$rekomendasiKelas) {
            return null;
        }

        $daftar = is_array($rekomendasiKelas->rekomendasi)
            ? $rekomendasiKelas->rekomendasi
            : json_decode((string) $rekomendasiKelas->rekomendasi, true);

        if (!is_array($daftar)) {
            return null;
        }

        foreach ($daftar as $entry) {
            if (($entry['nis'] ?? null) == $siswa->nis) {
                return [
                    'penyebab' => $entry['penyebab'] ?? [],
                    'saran' => $entry['saran'] ?? [],
                    'provider_used' => $rekomendasiKelas->provider_used ?? null,
                    'generated_at' => $rekomendasiKelas->generated_at,
                ];
            }
        }

        return null;
    }

    public function riwayatSiswa(int $siswaId, int $semesterId, ?string $dari = null, ?string $sampai = null): Collection
    {
        $sampai ??= Carbon::today()->toDateString();
        $dari ??= Carbon::parse($sampai)->subDays(29)->toDateString();

        return EarlyWarningResult::where('siswa_id', $siswaId)
            ->where('semester_id', $semesterId)
            ->whereBetween('tanggal_hitung', [$dari, $sampai])
            ->orderBy('tanggal_hitung')
            ->get();
    }
}