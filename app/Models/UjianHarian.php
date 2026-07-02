<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UjianHarian extends Model
{
    protected $fillable = [
        'guru_mapel_kelas_id',
        'bab_id',
        'judul',
        'tanggal_ujian',
        'durasi_menit',
        'status',
    ];

    protected $casts = [
        'tanggal_ujian' => 'date',
    ];

    public function guruMapelKelas()
    {
        return $this->belongsTo(GuruMapelKelas::class);
    }

    public function bab()
    {
        return $this->belongsTo(Bab::class);
    }

    public function soalUjians()
    {
        return $this->hasMany(SoalUjian::class);
    }

    public function hasilUjians()
    {
        return $this->hasMany(HasilUjian::class);
    }

    public function jawabanUjians()
    {
        return $this->hasMany(JawabanUjian::class);
    }
}
