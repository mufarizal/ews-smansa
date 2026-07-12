<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EarlyWarningResult extends Model
{
    public const KATEGORI = ['aman', 'perhatian', 'binaan'];

    public const KATEGORI_LABEL = [
        'aman' => 'Aman',
        'perhatian' => 'Perhatian',
        'binaan' => 'Binaan',
    ];

    public const URUTAN_KATEGORI = ['binaan' => 0, 'perhatian' => 1, 'aman' => 2];

    protected $fillable = [
        'siswa_id',
        'kelas_id',
        'semester_id',

        // Nilai mentah 3 kriteria
        'c1_akademik',       // Rata-rata (tugas + ujian) / 2
        'c2_absensi',        // % kehadiran dari total sesi mapel
        'c3_perilaku',       // max(0, 100 - total_poin_negatif)

        // Data perilaku untuk konteks AI
        'total_perilaku_negatif',
        'total_perilaku_positif',

        // Matriks ternormalisasi
        'r1_akademik',
        'r2_absensi',
        'r3_perilaku',

        // Hasil SAW
        'skor_akhir',
        'kategori',
        'data_tidak_lengkap',

        // Tracking scheduler
        'generated_at',
        'tanggal_hitung',
    ];

    protected $casts = [
        // Nilai mentah
        'c1_akademik' => 'float',
        'c2_absensi' => 'float',
        'c3_perilaku' => 'float',

        // Data perilaku
        'total_perilaku_negatif' => 'float',
        'total_perilaku_positif' => 'float',

        // Matriks normalisasi
        'r1_akademik' => 'float',
        'r2_absensi' => 'float',
        'r3_perilaku' => 'float',

        // Hasil
        'skor_akhir' => 'float',
        'data_tidak_lengkap' => 'boolean',
        'generated_at' => 'datetime',
        'tanggal_hitung' => 'date',
    ];

    // =========================================================================
    //  RELASI
    // =========================================================================

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    // =========================================================================
    //  SCOPE
    // =========================================================================

    public function scopeKelas($query, int $kelasId)
    {
        return $query->where('kelas_id', $kelasId);
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

    /**
     * Ambil hasil terbaru per siswa (snapshot harian terkini)
     */
    public function scopeTerbaru($query)
    {
        return $query->orderByDesc('tanggal_hitung');
    }

    /**
     * Filter berdasarkan tanggal hitung tertentu
     */
    public function scopeTanggal($query, string $tanggal)
    {
        return $query->where('tanggal_hitung', $tanggal);
    }

    // =========================================================================
    //  ACCESSOR
    // =========================================================================

    public function getKategoriLabelAttribute(): string
    {
        return $this->KATEGORI_LABEL[$this->kategori] ?? '-';
    }

    public function getKategoriBadgeColorAttribute(): string
    {
        return match ($this->kategori) {
            'aman' => 'green',
            'perhatian' => 'yellow',
            'binaan' => 'red',
            default => 'gray',
        };
    }

    /**
     * Persentase kehadiran dalam format readable
     */
    public function getKehadiranPersenAttribute(): string
    {
        return round($this->c2_absensi, 1).'%';
    }
}
