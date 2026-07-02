<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SoalTugas extends Model
{
    protected $fillable = [
        'tugas_id',
        'soal',
        'opsi_a',
        'opsi_b',
        'opsi_c',
        'opsi_d',
        'jawaban_benar',
        'bobot',
    ];

    public function tugas()
    {
        return $this->belongsTo(Tugas::class);
    }

    public function jawabanTugas()
    {
        return $this->hasMany(JawabanTugas::class);
    }
}
