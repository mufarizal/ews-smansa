<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuruPiket extends Model
{
    protected $table = 'guru_pikets';

    protected $fillable = [
        'guru_id',
        'semester_id',
        'hari',
        'catatan',
    ];

    // ============ Relasi ============

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    // ============ Scopes ============

    /**
     * Filter piket berdasarkan semester aktif
     */
    public function scopeActiveSemester($query)
    {
        return $query->whereHas('semester', fn($q) => $q->where('is_active', true));
    }

    /**
     * Filter piket berdasarkan semester tertentu
     */
    public function scopeForSemester($query, int $semesterId)
    {
        return $query->where('semester_id', $semesterId);
    }

    /**
     * Filter piket berdasarkan hari dalam semester aktif
     */
    public function scopeOnDay($query, string $hari)
    {
        return $query->where('hari', $hari)->activeSemester();
    }
}