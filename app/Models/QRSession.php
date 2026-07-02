<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QRSession extends Model
{
    protected $table = 'qr_sessions';

    protected $fillable = [
        'tanggal',
        'jam_batas',
        'jam_maksimal',
        'generated_at',
        'expire_at',
        'durasi_menit',
        'dibuat_oleh',
        'kelas_id',
        'mapel_id',
        'jenis_sesi',
        'tipe',  // masuk atau pulang
        'jumlah_hadir',
        'sudah_ditutup',
        'catatan',
        'kode_sesi',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jam_batas' => 'datetime',
        'jam_maksimal' => 'datetime',
        'generated_at' => 'datetime',
        'expire_at' => 'datetime',
        'sudah_ditutup' => 'boolean',
    ];

    const JENIS_HARIAN = 'harian';

    const JENIS_MAPEL = 'mapel';

    const TIPE_MASUK = 'masuk';

    const TIPE_PULANG = 'pulang';

    public function dibuat()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function mapel()
    {
        return $this->belongsTo(Mapel::class);
    }

    public function jadwal()
    {
        return $this->belongsTo(Jadwal::class);
    }

    public function absensis()
    {
        return $this->hasMany(Absensi::class, 'qr_session_id');
    }

    public function scopeForPiketDays($query, array $hariList)
    {
        $dayNames = collect($hariList)
            ->map(fn ($hari) => Guru::convertHariToEnglish((string) $hari))
            ->unique()
            ->values();

        if ($dayNames->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        $placeholders = implode(',', array_fill(0, $dayNames->count(), '?'));

        return $query->whereRaw("DAYNAME(tanggal) IN ({$placeholders})", $dayNames->all());
    }
}
