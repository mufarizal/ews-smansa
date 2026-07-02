<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SiswaCredentialsExport implements FromCollection, ShouldAutoSize, WithHeadings
{
    public function __construct(private readonly Collection $rows) {}

    public function collection(): Collection
    {
        return $this->rows->map(function (array $row) {
            return [
                'nis' => $row['nis'] ?? '',
                'nama' => $row['nama'] ?? '',
                'kelas' => $row['kelas'] ?? '',
                'email' => $row['email'] ?? '',
                'password' => $row['password'] ?? '',
            ];
        });
    }

    public function headings(): array
    {
        return ['NIS', 'Nama', 'Kelas', 'Email Login', 'Password Login'];
    }
}
