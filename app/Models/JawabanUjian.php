<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JawabanUjian extends Model
{
    protected $table = 'jawaban_ujians';

    protected $fillable = [
        'ujian_harian_id',
        'soal_ujian_id',
        'siswa_id',
        'jawaban',
        'is_benar',
    ];

    public function ujianHarian()
    {
        return $this->belongsTo(UjianHarian::class);
    }

    public function soalUjian()
    {
        return $this->belongsTo(SoalUjian::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}
