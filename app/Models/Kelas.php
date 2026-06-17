<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    protected $fillable = [
        'wali_kelas_id',
        'semester_id',
        'nama_kelas',
    ];

    public function siswas()
    {
        return $this->hasMany(Siswa::class);
    }

    public function waliKelas()
    {
        return $this->belongsTo(Guru::class, 'wali_kelas_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    // Get gurus teaching in this class
    public function gurus()
    {
        return $this->belongsToMany(Guru::class, 'guru_mapel_kelas')
            ->withPivot('mapel_id')
            ->distinct();
    }

    // Get subjects taught in this class
    public function mapels()
    {
        return $this->belongsToMany(Mapel::class, 'guru_mapel_kelas')
            ->distinct();
    }
}
