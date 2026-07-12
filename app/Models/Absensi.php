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
        'bab_id',
        'materi_id',
        'topik_pembelajaran',
        'sudah_disetujui',
        'catatan_persetujuan',
        'disetujui_oleh',
        // Catatan: kolom qr_session_id, device_id, latitude, longitude,
        // akurasi_meter, distance_meter, ip_address tetap ada di database
        // tapi tidak lagi di fillable karena tidak dipakai di flow baru.
        // Dihapus dari fillable agar tidak ada yang tidak sengaja mengisinya.
    ];

    protected $casts = [
        'jam_masuk' => 'datetime',
        'jam_pulang' => 'datetime',
        'tanggal' => 'date',
    ];

    const TIPE_HARIAN = 'harian';

    const TIPE_MAPEL = 'mapel';

    // =========================================================================
    //  RELASI
    // =========================================================================

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

    public function bab()
    {
        return $this->belongsTo(Bab::class);
    }

    public function materi()
    {
        return $this->belongsTo(Materi::class);
    }

    // Relasi qrSession() dihapus — QR tidak lagi dipakai di flow absensi SAW.
    // GuruPiket/QRController masih bisa query QRSession langsung via model QRSession.

    // =========================================================================
    //  SCOPE
    // =========================================================================

    /**
     * Filter absensi mapel saja (yang dipakai untuk kalkulasi SAW)
     */
    public function scopeMapel($query)
    {
        return $query->where('tipe', self::TIPE_MAPEL);
    }

    /**
     * Filter berdasarkan semester aktif via jadwal
     */
    public function scopeSemesterAktif($query)
    {
        $semester = Semester::where('is_active', true)->first();

        if (! $semester) {
            return $query->whereRaw('0 = 1');
        }

        return $query->whereHas('jadwal', function ($q) use ($semester) {
            $q->where('semester_id', $semester->id);
        });
    }

    /**
     * Hitung persentase kehadiran siswa dari absensi mapel
     * dalam periode tertentu
     */
    public static function hitungPersenKehadiran(int $siswaId, ?int $semesterId = null): float
    {
        $query = self::where('siswa_id', $siswaId)
            ->where('tipe', self::TIPE_MAPEL);

        if ($semesterId) {
            $query->whereHas('jadwal', fn ($q) => $q->where('semester_id', $semesterId));
        }

        $total = (clone $query)->count();

        if ($total === 0) {
            return 0;
        }

        $hadir = (clone $query)->where('status', 'hadir')->count();

        return round(($hadir / $total) * 100, 2);
    }
}
