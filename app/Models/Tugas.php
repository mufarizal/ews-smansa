<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tugas extends Model
{
    protected $fillable = [
        'guru_mapel_kelas_id',
        'materi_id',
        'judul',
        'deskripsi',
        'tanggal_tugas',
        'tanggal_deadline',
        'jenis',
        'link_meeting',
    ];

    protected $casts = [
        'tanggal_tugas' => 'date',
        'tanggal_deadline' => 'date',
    ];

    public function guruMapelKelas()
    {
        return $this->belongsTo(GuruMapelKelas::class);
    }

    public function materi()
    {
        return $this->belongsTo(Materi::class);
    }

    public function nilaiTugas()
    {
        return $this->hasMany(NilaiTugas::class);
    }

    public function soalTugas()
    {
        return $this->hasMany(SoalTugas::class);
    }
}
