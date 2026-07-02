<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class JadwalKegiatan extends Model
{
    protected $fillable = [
        'semester_id',
        'hari',
        'minggu_ke',
        'nama_kegiatan',
        'jam_mulai',
        'jam_selesai',
        'is_active',
        'catatan',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'minggu_ke' => 'integer',
    ];

    // ============ Relasi ============

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    // ============ Scopes ============

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->whereHas('semester', fn ($q) => $q->where('is_active', true));
    }

    public function scopeForSemester($query, int $semesterId)
    {
        return $query->where('semester_id', $semesterId);
    }

    // ============ Static Helpers ============

    /**
     * Ambil kegiatan yang berlaku pada tanggal tertentu.
     * Cocokkan hari + minggu_ke dalam bulan.
     */
    public static function forDate(Carbon $date): ?self
    {
        $hari = Jadwal::carbonToHari($date);
        $mingguKe = (int) ceil($date->day / 7);

        return self::where('hari', $hari)
            ->where('minggu_ke', $mingguKe)
            ->where('is_active', true)
            ->whereHas('semester', fn ($q) => $q->where('is_active', true))
            ->first();
    }

    /**
     * Ambil semua kegiatan dalam satu semester, diurutkan per hari + minggu_ke
     */
    public static function forSemesterOrdered(int $semesterId): Collection
    {
        return self::where('semester_id', $semesterId)
            ->orderByRaw("CASE hari
                WHEN 'Senin'  THEN 1 WHEN 'Selasa' THEN 2 WHEN 'Rabu'   THEN 3
                WHEN 'Kamis'  THEN 4 WHEN 'Jumat'  THEN 5 WHEN 'Sabtu'  THEN 6
                ELSE 99 END")
            ->orderBy('minggu_ke')
            ->orderBy('jam_mulai')
            ->get();
    }

    // ============ Accessors ============

    public function getMingguKeLabelAttribute(): string
    {
        return match ($this->minggu_ke) {
            1 => 'Minggu ke-1',
            2 => 'Minggu ke-2',
            3 => 'Minggu ke-3',
            4 => 'Minggu ke-4',
            default => '-',
        };
    }

    public function getAttendanceWindowLabel(): string
    {
        return $this->jam_mulai.' - '.$this->jam_selesai;
    }

    /**
     * Cek apakah jam kegiatan ini selesai sebelum jam mapel pertama dimulai.
     * Dipakai untuk validasi agar kegiatan tidak overlap dengan jadwal mapel.
     */
    public function isBeforeMapelJam(string $jamMapelMulai): bool
    {
        return $this->jam_selesai <= $jamMapelMulai;
    }
}
