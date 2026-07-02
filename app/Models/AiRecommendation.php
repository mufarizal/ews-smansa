<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiRecommendation extends Model
{
    protected $table = 'ai_recommendations';

    protected $fillable = [
        'semester_id',
        'scope',
        'scope_id',
        'kategori',
        'rekomendasi',
        'provider_used',
        'generated_at',
    ];

    protected $casts = [
        'rekomendasi' => 'array',
        'generated_at' => 'datetime',
    ];

    // =========================================================================
    //  RELASI
    // =========================================================================

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'scope_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'scope_id');
    }

    // =========================================================================
    //  SCOPE
    // =========================================================================

    public function scopeUntukKelas($query, int $kelasId)
    {
        return $query->where('scope', 'kelas')->where('scope_id', $kelasId);
    }

    public function scopeUntukSiswa($query, int $siswaId)
    {
        return $query->where('scope', 'siswa')->where('scope_id', $siswaId);
    }

    public function scopeSemesterAktif($query)
    {
        $semester = Semester::where('is_active', true)->first();

        return $semester
            ? $query->where('semester_id', $semester->id)
            : $query->whereRaw('0 = 1');
    }

    public function scopeKategori($query, string $kategori)
    {
        return $query->where('kategori', $kategori);
    }

    // =========================================================================
    //  STATIC HELPER
    // =========================================================================

    /**
     * Ambil rekomendasi AI terbaru untuk satu kelas di semester aktif.
     * Return collection di-keyBy kategori.
     */
    public static function untukKelasSekarang(int $kelasId): \Illuminate\Support\Collection
    {
        $semester = Semester::where('is_active', true)->first();

        if (!$semester) {
            return collect();
        }

        return static::where('scope', 'kelas')
            ->where('scope_id', $kelasId)
            ->where('semester_id', $semester->id)
            ->get()
            ->keyBy('kategori');
    }

    /**
     * Ambil rekomendasi AI terbaru untuk satu siswa di semester aktif.
     */
    public static function untukSiswaSekarang(int $siswaId): ?static
    {
        $semester = Semester::where('is_active', true)->first();

        if (!$semester) {
            return null;
        }

        return static::where('scope', 'siswa')
            ->where('scope_id', $siswaId)
            ->where('semester_id', $semester->id)
            ->orderByDesc('generated_at')
            ->first();
    }
}