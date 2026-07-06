<?php

namespace App\Exports;

use App\Models\Absensi;
use App\Models\AiRecommendation;
use App\Models\EarlyWarningResult;
use App\Models\Semester;
use App\Models\Siswa;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class WaliKelasSiswaExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private readonly int $kelasId) {}

    public function collection(): Collection
    {
        $semester = Semester::where('is_active', true)->firstOrFail();

        $siswa = Siswa::with(['kelas', 'user'])
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
            ->where('kelas_id', $this->kelasId)
            ->orderBy('nama')
            ->orderBy('nis')
            ->get();

        $snapshot = EarlyWarningResult::where('kelas_id', $this->kelasId)
            ->where('semester_id', $semester->id)
            ->orderByDesc('tanggal_hitung')
            ->get()
            ->unique('siswa_id')
            ->keyBy('siswa_id');

        $siswaIds = $snapshot->pluck('siswa_id')->filter()->values()->toArray();
        $tanggalTerbaru = $snapshot->first()->tanggal_hitung ?? null;
        $trendsBatch = $tanggalTerbaru
            ? $this->trendHarianBatch($siswaIds, $semester->id, $tanggalTerbaru)
            : collect();

        $rekomendasiByKategori = [];
        foreach (['binaan', 'perhatian', 'aman'] as $kategori) {
            $rekomendasiByKategori[$kategori] = AiRecommendation::where('scope', 'kelas')
                ->where('scope_id', $this->kelasId)
                ->where('kategori', $kategori)
                ->where('semester_id', $semester->id)
                ->latest('generated_at')
                ->first();
        }

        return $siswa->map(function ($item) use ($snapshot, $trendsBatch, $rekomendasiByKategori) {
            $hasilSaw = $snapshot->get($item->id);

            $trend = $hasilSaw
                ? ($trendsBatch->get($item->id) ?? ['arah' => 'tetap', 'selisih' => 0.0, 'skor_sebelumnya' => null])
                : null;

            $rekomendasi = null;
            if ($hasilSaw && ! empty($hasilSaw->kategori) && ! empty($rekomendasiByKategori[$hasilSaw->kategori])) {
                $record = $rekomendasiByKategori[$hasilSaw->kategori];
                $daftar = is_array($record->rekomendasi)
                    ? $record->rekomendasi
                    : json_decode((string) $record->rekomendasi, true);

                if (is_array($daftar)) {
                    $nis = $item->nis ?? null;
                    foreach ($daftar as $entry) {
                        if (($entry['nis'] ?? null) == $nis) {
                            $rekomendasi = $entry;
                            break;
                        }
                    }
                }
            }

            $penyebab = $rekomendasi['penyebab'] ?? [];
            $saran = $rekomendasi['saran'] ?? [];

            return [
                'nis' => $item->nis,
                'nama' => $item->nama,
                'kategori' => $hasilSaw->kategori ?? '-',
                'skor_akhir' => $hasilSaw->skor_akhir ?? null,
                'tren_harian' => $trend['arah'] ?? '-',
                'arah_tren' => $trend['arah'] ?? '-',
                'selisih' => $trend['selisih'] ?? 0,
                'ada_rekomendasi' => (! empty($penyebab) || ! empty($saran)) ? 'Ya' : 'Tidak',
                'penyebab' => implode("\n", $penyebab),
                'saran' => implode("\n", $saran),
                'nilai_tugas' => $item->nilai_tugas_avg > 0 ? round($item->nilai_tugas_avg) : null,
                'nilai_ujian' => $item->nilai_ujian_avg > 0 ? round($item->nilai_ujian_avg) : null,
                'hadir_absensi' => (int) ($item->hadir_absensi_count ?? 0),
                'total_absensi' => (int) ($item->total_absensi_count ?? 0),
                'keterlambatan_menit' => (int) ($item->total_keterlambatan_menit ?? 0),
                'catatan_perilaku' => (int) ($item->catatan_perilaku_count ?? 0),
                'alamat' => $item->alamat ?? '-',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'No',
            'NIS',
            'Nama Siswa',
            'Kategori SAW',
            'Skor SAW',
            'Tren Harian',
            'Arah Tren',
            'Selisih',
            'Ada Rekomendasi AI',
            'Penyebab (AI)',
            'Saran (AI)',
            'Nilai Tugas',
            'Nilai Ujian',
            'Absensi Hadir/Total',
            'Keterlambatan (mnt)',
            'Catatan Perilaku',
            'Alamat',
        ];
    }

    public function map($row): array
    {
        static $count = 0;
        $count++;

        return [
            $count,
            $row['nis'],
            $row['nama'],
            ucfirst($row['kategori']),
            $row['skor_akhir'] !== null ? number_format($row['skor_akhir'], 2) : '-',
            ucfirst($row['tren_harian']),
            ucfirst($row['arah_tren']),
            number_format($row['selisih'], 2),
            $row['ada_rekomendasi'],
            $row['penyebab'] ?: '-',
            $row['saran'] ?: '-',
            $row['nilai_tugas'] !== null ? $row['nilai_tugas'] : '-',
            $row['nilai_ujian'] !== null ? $row['nilai_ujian'] : '-',
            $row['hadir_absensi'].'/'.$row['total_absensi'],
            $row['keterlambatan_menit'].' mnt',
            $row['catatan_perilaku'],
            $row['alamat'],
        ];
    }

    private function trendHarianBatch(array $siswaIds, int $semesterId, string $tanggalTerbaru): Collection
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

            if (! $sebelumnya) {
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
}
