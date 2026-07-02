<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HasilUjian extends Model
{
protected $fillable = [
    'ujian_harian_id',
    'siswa_id',
    'jumlah_benar',
    'jumlah_salah',
    'nilai',
];

public function ujianHarian()
{
    return $this->belongsTo(UjianHarian::class);
}

public function siswa()
{
    return $this->belongsTo(Siswa::class);
}
}
