<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    protected $fillable = [
        'siswa_id',
        'tanggal',
        'tipe',
        'status',
        'jam_masuk',
        'jam_pulang',
        'terlambat_menit',
        'jadwal_id',
        'guru_id',
        'mapel_id',
        'ip_address',
        'qr_session_id',
        'device_id',
        'latitude',
        'longitude',
        'akurasi_meter',
        'distance_meter',
    ];

    protected $casts = [
        'jam_masuk' => 'datetime',
        'jam_pulang' => 'datetime',
        'tanggal' => 'date',
    ];

    const TIPE_HARIAN = 'harian';
    const TIPE_MAPEL = 'mapel';

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function jadwal()
    {
        return $this->belongsTo(Jadwal::class);
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    public function mapel()
    {
        return $this->belongsTo(Mapel::class);
    }

    public function qrSession()
    {
        return $this->belongsTo(QRSession::class);
    }
}
