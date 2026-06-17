<?php

namespace App\Exports;

use App\Models\Absensi;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Carbon\Carbon;

class AttendanceReportExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public $qrSessionId;
    public $status;
    public $kelasId;
    public $search;

    public function __construct($qrSessionId = null, $status = null, $kelasId = null, $search = null)
    {
        $this->qrSessionId = $qrSessionId;
        $this->status = $status;
        $this->kelasId = $kelasId;
        $this->search = $search;
    }

    public function query()
    {
        $query = Absensi::where('tipe', 'harian')
            ->with(['siswa', 'siswa.kelas']);

        if ($this->qrSessionId) {
            $query->where('qr_session_id', $this->qrSessionId);
        }

        if ($this->status && $this->status != 'semua') {
            $query->where('status', $this->status);
        }

        if ($this->kelasId) {
            $query->whereHas('siswa', function ($subQuery) {
                $subQuery->where('kelas_id', $this->kelasId);
            });
        }

        if ($this->search) {
            $query->whereHas('siswa', function ($subQuery) {
                $subQuery->where('nama', 'like', '%' . $this->search . '%')
                    ->orWhere('nis', 'like', '%' . $this->search . '%');
            });
        }

        return $query->orderBy('siswa_id');
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Siswa',
            'Kelas',
            'Jam Masuk',
            'Jam Pulang',
            'Terlambat (menit)',
            'Status',
            'Akurasi GPS (meter)',
            'Tanggal',
        ];
    }

    public function map($row): array
    {
        static $count = 0;
        $count++;

        return [
            $count,
            $row->siswa->nama ?? 'N/A',
            $row->siswa->kelas->nama_kelas ?? 'N/A',
            $row->jam_masuk ? Carbon::parse($row->jam_masuk)->format('H:i:s') : '-',
            $row->jam_pulang ? Carbon::parse($row->jam_pulang)->format('H:i:s') : '-',
            $row->terlambat_menit ?? 0,
            ucfirst($row->status ?? 'unknown'),
            $row->akurasi_meter ?? 'N/A',
            optional($row->tanggal)?->format('d-m-Y') ?? date('d-m-Y', strtotime($row->tanggal)),
        ];
    }
}
