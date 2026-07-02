<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuruMapelKelas extends Model
{
    protected $table = 'guru_mapel_kelas';
    protected $fillable = [
        'guru_id',
        'semester_id',
        'mapel_id',
        'kelas_id',
    ];

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    public function mapel()
    {
        return $this->belongsTo(Mapel::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function tugas()
    {
        return $this->hasMany(Tugas::class);
    }

    public function ujianHarians()
    {
        return $this->hasMany(UjianHarian::class);
    }

    public function babs()
    {
        return $this->hasMany(Bab::class);
    }
}
