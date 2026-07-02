<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerilakuSiswa extends Model
{
    protected $fillable = [
        'siswa_id',
        'perilaku_id',
        'guru_id',
        'tanggal',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function perilaku()
    {
        return $this->belongsTo(Perilaku::class);
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }
}
