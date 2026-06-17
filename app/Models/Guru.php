<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    protected $fillable = [
        'user_id',
        'nip',
        'nama',
        'no_hp',
    ];

    // ============ Relasi ============

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function guruMapelKelas()
    {
        return $this->hasMany(GuruMapelKelas::class);
    }

    public function guruPikets()
    {
        return $this->hasMany(GuruPiket::class);
    }

    public function guruBkKelas()
    {
        return $this->hasMany(GuruBkKelas::class);
    }

    public function jadwals()
    {
        return $this->hasMany(Jadwal::class);
    }

    /**
     * Kelas yang diampu sebagai wali kelas
     */
    public function kelasDiampu()
    {
        return $this->hasMany(Kelas::class, 'wali_kelas_id');
    }

    public function mapels()
    {
        return $this->belongsToMany(Mapel::class, 'guru_mapel_kelas')
            ->withPivot(['kelas_id', 'semester_id']);
    }

    public function kelasDiajar()
    {
        return $this->belongsToMany(Kelas::class, 'guru_mapel_kelas')
            ->distinct();
    }

    // ============ Helper Methods ============

    /**
     * Ambil semua hari piket guru di semester tertentu
     */
    public function getPiketDays(int $semesterId): array
    {
        return $this->guruPikets()
            ->forSemester($semesterId)
            ->pluck('hari')
            ->unique()
            ->sort()
            ->values()
            ->toArray();
    }

    /**
     * Ambil hari piket guru di semester yang sedang aktif
     */
    public function getPiketDaysActiveSemester(): array
    {
        return $this->guruPikets()
            ->activeSemester()
            ->pluck('hari')
            ->unique()
            ->sort()
            ->values()
            ->toArray();
    }

    /**
     * Cek apakah guru piket pada hari tertentu di semester aktif
     */
    public function isPiketOnDay(string $hari): bool
    {
        return $this->guruPikets()
            ->where('hari', $hari)
            ->activeSemester()
            ->exists();
    }

    /**
     * Cek apakah guru piket pada hari tertentu di semester tertentu
     */
    public function isPiketOnDayInSemester(string $hari, int $semesterId): bool
    {
        return $this->guruPikets()
            ->where('hari', $hari)
            ->forSemester($semesterId)
            ->exists();
    }
}