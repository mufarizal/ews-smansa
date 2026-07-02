<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JawabanTugas extends Model
{
    protected $fillable = [
        'tugas_id',
        'soal_tugas_id',
        'siswa_id',
        'jawaban',
        'is_benar',
    ];

    protected $casts = [
        'is_benar' => 'boolean',
    ];

    public function nilaiTugas()
    {
        return $this->belongsTo(NilaiTugas::class);
    }

    public function tugas()
    {
        return $this->belongsTo(Tugas::class);
    }

    public function soalTugas()
    {
        return $this->belongsTo(SoalTugas::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}
