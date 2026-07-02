<?php

namespace App\Imports;

use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SiswaBulkImport implements ToCollection, WithHeadingRow
{
    public Collection $rows;

    public array $errors = [];

    // Cache kelas agar tidak query berulang
    private array $kelasCache = [];

    public function collection(Collection $rows): void
    {
        $this->rows = $rows;
    }

    public function import(): array
    {
        $imported = 0;
        $skipped = 0;

        foreach ($this->rows as $index => $row) {
            $kelasId = $row['kelas_id'] ?? null;
            $nama = trim($row['nama'] ?? '');
            $alamat = trim($row['alamat'] ?? '');

            if (empty($kelasId) || empty($nama)) {
                $skipped++;

                continue;
            }

            // Generate NIS otomatis berdasarkan kelas
            $nis = $this->generateNis((int) $kelasId);

            if (! $nis) {
                $this->errors[] = 'Baris '.($index + 2).": kelas_id {$kelasId} tidak ditemukan.";
                $skipped++;

                continue;
            }

            Siswa::create([
                'kelas_id' => $kelasId,
                'nis' => $nis,
                'nama' => $nama,
                'alamat' => $alamat ?: null,
            ]);

            $imported++;
        }

        return ['imported' => $imported, 'skipped' => $skipped];
    }

    private function generateNis(int $kelasId): ?string
    {
        // Cache kelas supaya tidak query terus
        if (! isset($this->kelasCache[$kelasId])) {
            $this->kelasCache[$kelasId] = Kelas::find($kelasId);
        }

        $kelas = $this->kelasCache[$kelasId];
        if (! $kelas) {
            return null;
        }

        // Ambil kode dari nama_kelas, contoh: "10 A" → "101"
        preg_match('/(\d+)\s*([A-Za-z])/', $kelas->nama_kelas, $m);
        $tingkat = $m[1] ?? '10';
        $nomorHuruf = isset($m[2]) ? (ord(strtoupper($m[2])) - ord('A') + 1) : 1;
        $kodeKelas = $tingkat.$nomorHuruf;

        // Hitung absen berikutnya untuk kelas ini
        $absen = Siswa::where('kelas_id', $kelasId)->count() + 1;

        return $kodeKelas.str_pad($absen, 2, '0', STR_PAD_LEFT);
    }
}
