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
        return $this->normalizeHariList(
            $this->guruPikets()
                ->forSemester($semesterId)
                ->pluck('hari')
                ->unique()
                ->sort()
                ->values()
                ->toArray()
        );
    }

    /**
     * Ambil hari piket guru di semester yang sedang aktif
     */
    public function getPiketDaysActiveSemester(): array
    {
        return $this->normalizeHariList(
            $this->guruPikets()
                ->activeSemester()
                ->pluck('hari')
                ->unique()
                ->sort()
                ->values()
                ->toArray()
        );
    }

    /**
     * Cek apakah guru piket pada hari tertentu.
     * Menerima hari bahasa Indonesia dan tetap kompatibel dengan data lama bahasa Inggris.
     */
    public function isPiketOnDay(string $hari): bool
    {
        $normalizedHari = self::convertHariToIndonesia($hari);
        $englishHari = self::convertHariToEnglish($normalizedHari);

        return $this->guruPikets()
            ->whereIn('hari', [$normalizedHari, $englishHari])
            ->activeSemester()
            ->exists();
    }

    /**
     * Konversi hari bahasa Indonesia ke bahasa Inggris
     */
    public static function convertHariToEnglish(string $hari): string
    {
        return match (self::convertHariToIndonesia($hari)) {
            'Senin' => 'Monday',
            'Selasa' => 'Tuesday',
            'Rabu' => 'Wednesday',
            'Kamis' => 'Thursday',
            'Jumat' => 'Friday',
            'Sabtu' => 'Saturday',
            'Minggu' => 'Sunday',
            default => $hari,
        };
    }

    /**
     * Konversi hari bahasa Inggris ke bahasa Indonesia
     */
    public static function convertHariToIndonesia(string $hari): string
    {
        return match ($hari) {
            'Monday', 'Senin' => 'Senin',
            'Tuesday', 'Selasa' => 'Selasa',
            'Wednesday', 'Rabu' => 'Rabu',
            'Thursday', 'Kamis' => 'Kamis',
            'Friday', 'Jumat' => 'Jumat',
            'Saturday', 'Sabtu' => 'Sabtu',
            'Sunday', 'Minggu' => 'Minggu',
            default => $hari,
        };
    }

    private function normalizeHariList(array $hariList): array
    {
        return collect($hariList)
            ->map(fn ($hari) => self::convertHariToIndonesia((string) $hari))
            ->unique()
            ->sort()
            ->values()
            ->toArray();
    }
}
