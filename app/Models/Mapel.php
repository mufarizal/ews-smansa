<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mapel extends Model
{
    protected $fillable = [
        'nama',
    ];

    public function gurus()
    {
        return $this->belongsToMany(Guru::class, 'guru_mapel_kelas')
            ->withPivot('kelas_id')
            ->with('kelas');
    }

    public function guruMapelKelas()
    {
        return $this->hasMany(GuruMapelKelas::class);
    }
}
