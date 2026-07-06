<?php

namespace App\Exports;

use App\Models\AiRecommendation;
use App\Models\EarlyWarningResult;
use App\Models\Semester;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MonitoringSiswaExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(
        private readonly int $kelasId,
        private readonly ?string $kategoriFilter = null
    ) {}

    public function collection(): Collection
    {
        $semester = Semester::where('is_active', true)->firstOrFail();

        $query = EarlyWarningResult::with(['siswa'])
            ->where('kelas_id', $this->kelasId)
            ->where('semester_id', $semester->id)
            ->orderByDesc('tanggal_hitung');

        if ($this->kategoriFilter) {
            $query->where('kategori', $this->kategoriFilter);
        }

        $snapshot = $query->get()->unique('siswa_id');

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

        return $snapshot->map(function ($item) use ($trendsBatch, $rekomendasiByKategori) {
            $trend = $trendsBatch->get($item->siswa_id, [
                'arah' => 'tetap',
                'selisih' => 0.0,
                'skor_sebelumnya' => null,
            ]);

            $rekomendasi = null;
            if (! empty($item->kategori) && ! empty($rekomendasiByKategori[$item->kategori])) {
                $record = $rekomendasiByKategori[$item->kategori];
                $daftar = is_array($record->rekomendasi)
                    ? $record->rekomendasi
                    : json_decode((string) $record->rekomendasi, true);

                if (is_array($daftar)) {
                    $nis = $item->siswa->nis ?? null;
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
                'nis' => $item->siswa->nis ?? '-',
                'nama' => $item->siswa->nama ?? '-',
                'kategori' => $item->kategori ?? '-',
                'skor_akhir' => (float) ($item->skor_akhir ?? 0),
                'tren_harian' => $trend['arah'] ?? 'tetap',
                'arah_tren' => $trend['arah'] ?? 'tetap',
                'selisih' => (float) ($trend['selisih'] ?? 0),
                'skor_sebelumnya' => $trend['skor_sebelumnya'] !== null ? (float) $trend['skor_sebelumnya'] : null,
                'data_lengkap' => empty($item->data_tidak_lengkap) ? 'Ya' : 'Tidak',
                'ada_rekomendasi' => (! empty($penyebab) || ! empty($saran)) ? 'Ya' : 'Tidak',
                'penyebab' => implode("\n", $penyebab),
                'saran' => implode("\n", $saran),
            ];
        })
            ->sortBy('nama')
            ->values();
    }

    public function headings(): array
    {
        return [
            'No',
            'NIS',
            'Nama Siswa',
            'Kategori',
            'Skor Akhir',
            'Tren Harian',
            'Arah Tren',
            'Selisih',
            'Skor Sebelumnya',
            'Data Lengkap',
            'Ada Rekomendasi AI',
            'Penyebab (AI)',
            'Saran (AI)',
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
            number_format($row['skor_akhir'], 2),
            ucfirst($row['tren_harian']),
            ucfirst($row['arah_tren']),
            number_format($row['selisih'], 2),
            $row['skor_sebelumnya'] !== null ? number_format($row['skor_sebelumnya'], 2) : '-',
            $row['data_lengkap'],
            $row['ada_rekomendasi'],
            $row['penyebab'] ?: '-',
            $row['saran'] ?: '-',
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
