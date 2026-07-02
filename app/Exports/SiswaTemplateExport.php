<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;

class SiswaTemplateExport implements WithMultipleSheets
{
    public function __construct(private readonly Collection $kelas) {}

    public function sheets(): array
    {
        return [
            new SiswaTemplateDataSheetExport,
            new SiswaTemplateGuideSheetExport($this->kelas),
        ];
    }
}

class SiswaTemplateDataSheetExport implements FromArray, ShouldAutoSize, WithTitle
{
    public function array(): array
    {
        return [
            ['kelas_id', 'nis', 'nama', 'alamat'],
        ];
    }

    public function title(): string
    {
        return 'Template Import';
    }
}

class SiswaTemplateGuideSheetExport implements FromArray, ShouldAutoSize, WithTitle
{
    public function __construct(private readonly Collection $kelas) {}

    public function array(): array
    {
        $rows = [
            ['PANDUAN PENGISIAN TEMPLATE SISWA'],
            ['1. Isi data hanya di sheet: Template Import'],
            ['2. Kolom wajib: kelas_id, nis, nama'],
            ['3. Kolom alamat boleh dikosongkan'],
            ['4. Gunakan kelas_id sesuai daftar referensi di bawah ini'],
            ['5. NIS harus unik dan belum terdaftar di sistem'],
            ['6. Jangan ubah nama header kolom pada sheet Template Import'],
            [''],
            ['CONTOH DATA YANG BENAR'],
            ['kelas_id', 'nis', 'nama', 'alamat'],
            [
                $this->kelas->first()?->id ?? 1,
                '1234567890',
                'Nama Siswa',
                'Alamat lengkap siswa',
            ],
            [''],
            ['DAFTAR KELAS TERSEDIA'],
            ['kelas_id', 'nama_kelas'],
        ];

        foreach ($this->kelas as $item) {
            $rows[] = [$item->id, $item->nama_kelas];
        }

        if ($this->kelas->isEmpty()) {
            $rows[] = ['-', 'Belum ada data kelas', '-', '-'];
        }

        return $rows;
    }

    public function title(): string
    {
        return 'Panduan & Referensi';
    }
}
