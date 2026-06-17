<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    protected $fillable = [
        'semester_id',
        'kelas_id',
        'guru_id',
        'mapel_id',
        'hari',
        'jam_mulai',
        'jam_selesai',
        'minggu_ke',
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

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    public function mapel()
    {
        return $this->belongsTo(Mapel::class);
    }

    public function absensis()
    {
        return $this->hasMany(Absensi::class);
    }

    // ============ Scopes ============

    /**
     * Jadwal aktif dalam semester aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->whereHas('semester', fn($q) => $q->where('is_active', true));
    }

    /**
     * Filter berdasarkan semester tertentu
     */
    public function scopeForSemester($query, int $semesterId)
    {
        return $query->where('semester_id', $semesterId);
    }

    /**
     * Filter jadwal untuk hari tertentu (semester aktif)
     */
    public function scopeForDay($query, string $hari)
    {
        return $query->where('hari', $hari)->active();
    }

    /**
     * Filter jadwal yang berlaku pada tanggal tertentu
     * Mempertimbangkan minggu_ke dan tanggal semester
     */
    public function scopeForDate($query, Carbon $date)
    {
        $hari = self::carbonToHari($date);

        return $query->active()
            ->where('hari', $hari)
            ->where(function ($q) use ($date) {
                // NULL = berlaku setiap minggu
                $q->whereNull('minggu_ke')
                    // Atau minggu_ke cocok dengan minggu ke-N tanggal tersebut
                    ->orWhere('minggu_ke', self::getMingguKeDalamBulan($date));
            });
    }

    // ============ Methods ============

    /**
     * Cek apakah jadwal berlaku pada tanggal tertentu
     */
    public function berlakuPadaTanggal(Carbon $date): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // Cek hari cocok
        if ($this->hari !== self::carbonToHari($date)) {
            return false;
        }

        // Cek semester masih aktif dan tanggal dalam range semester
        $semester = $this->semester;
        if (!$semester || !$semester->is_active) {
            return false;
        }

        if ($semester->tanggal_mulai && $date->lt(Carbon::parse($semester->tanggal_mulai))) {
            return false;
        }

        if ($semester->tanggal_selesai && $date->gt(Carbon::parse($semester->tanggal_selesai))) {
            return false;
        }

        // Cek minggu_ke — null berarti setiap minggu
        if (!is_null($this->minggu_ke)) {
            return self::getMingguKeDalamBulan($date) === $this->minggu_ke;
        }

        return true;
    }

    /**
     * Cek apakah attendance window terbuka sekarang
     */
    public function isAttendanceWindowOpen(?Carbon $moment = null): bool
    {
        $moment ??= Carbon::now();

        if (!$this->berlakuPadaTanggal($moment->copy()->startOfDay())) {
            return false;
        }

        $start = Carbon::parse($moment->toDateString() . ' ' . $this->jam_mulai, config('app.timezone', 'Asia/Jakarta'));
        $end = Carbon::parse($moment->toDateString() . ' ' . $this->jam_selesai, config('app.timezone', 'Asia/Jakarta'));

        return $moment->betweenIncluded($start, $end);
    }

    public function getAttendanceWindowLabel(): string
    {
        return $this->jam_mulai . ' - ' . $this->jam_selesai;
    }

    /**
     * Label pola minggu untuk display
     */
    public function getMingguKeLabelAttribute(): string
    {
        return match ($this->minggu_ke) {
            1 => 'Minggu ke-1',
            2 => 'Minggu ke-2',
            3 => 'Minggu ke-3',
            4 => 'Minggu ke-4',
            default => 'Setiap minggu',
        };
    }

    // ============ Helpers (static) ============

    /**
     * Hitung minggu ke-N dalam bulan dari sebuah tanggal
     * cth: 7 Juni → minggu ke-2 (karena minggu pertama adalah 1-7)
     */
    public static function getMingguKeDalamBulan(Carbon $date): int
    {
        return (int) ceil($date->day / 7);
    }

    public static function carbonToHari(Carbon $date): string
    {
        return match ($date->dayOfWeekIso) {
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            default => 'Minggu',
        };
    }
}