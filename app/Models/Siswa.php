<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    protected $fillable = [
        'user_id',
        'kelas_id',
        'nis',
        'nama',
        'alamat',
    ];

    // =========================================================================
    //  RELASI
    // =========================================================================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function absensis()
    {
        return $this->hasMany(Absensi::class);
    }

    public function absensisMapel()
    {
        return $this->hasMany(Absensi::class)->where('tipe', Absensi::TIPE_MAPEL);
    }

    public function nilaiTugas()
    {
        return $this->hasMany(NilaiTugas::class);
    }

    public function perilakuSiswas()
    {
        return $this->hasMany(PerilakuSiswa::class);
    }

    public function jawabanUjians()
    {
        return $this->hasMany(JawabanUjian::class);
    }

    public function hasilUjians()
    {
        return $this->hasMany(HasilUjian::class);
    }

    public function earlyWarningResults()
    {
        return $this->hasMany(EarlyWarningResult::class);
    }

    // =========================================================================
    //  ACCESSOR
    // =========================================================================

    /**
     * Rata-rata nilai tugas siswa.
     */
    public function getNilaiTugasAvgAttribute(): float
    {
        return (float) $this->nilaiTugas()->whereNotNull('nilai')->avg('nilai');
    }

    /**
     * Rata-rata nilai ujian siswa.
     */
    public function getNilaiUjianAvgAttribute(): float
    {
        return (float) $this->hasilUjians()->whereNotNull('nilai')->avg('nilai');
    }

    /**
     * Persentase kehadiran siswa — dihitung dari absensi MAPEL saja.
     * Absensi harian (QR) tidak lagi masuk indikator SAW.
     *
     * @param int|null $semesterId Filter per semester, null = semua
     */
    public function getKehadiranPersenAttribute(): float
    {
        return $this->hitungKehadiranPersen();
    }

    /**
     * Hitung persentase kehadiran dengan opsi filter semester.
     */
    public function hitungKehadiranPersen(?int $semesterId = null): float
    {
        $query = $this->absensis()->where('tipe', Absensi::TIPE_MAPEL);

        if ($semesterId) {
            $query->whereHas('jadwal', fn($q) => $q->where('semester_id', $semesterId));
        }

        $total = (clone $query)->count();

        if ($total === 0) {
            return 0.0;
        }

        $hadir = (clone $query)->where('status', 'hadir')->count();

        return round(($hadir / $total) * 100, 2);
    }

    /**
     * Total menit keterlambatan siswa (dari absensi mapel).
     */
    public function getTotalKeterlambatanAttribute(): int
    {
        return (int) $this->absensis()
            ->where('tipe', Absensi::TIPE_MAPEL)
            ->where('status', 'terlambat')
            ->sum('terlambat_menit');
    }

    /**
     * Total skor perilaku siswa (positif - negatif).
     * Positif menambah, negatif mengurangi.
     */
    public function getTotalSkorPerilakuAttribute(): int
    {
        return (int) $this->perilakuSiswas()
            ->with('perilaku')
            ->get()
            ->sum(fn($p) => $p->perilaku->poin ?? 0);
    }

    /**
     * Hasil SAW terbaru siswa di semester aktif.
     */
    public function getHasilSawTerbaruAttribute(): ?EarlyWarningResult
    {
        $semester = Semester::where('is_active', true)->first();

        if (!$semester) {
            return null;
        }

        return $this->earlyWarningResults()
            ->where('semester_id', $semester->id)
            ->orderByDesc('tanggal_hitung')
            ->first();
    }
}