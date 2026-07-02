<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Perilaku extends Model
{
    protected $fillable = [
        'nama_perilaku',
        'jenis',
        'poin',
        'status_aktif',
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
    ];

    public function perilakuSiswas()
    {
        return $this->hasMany(PerilakuSiswa::class);
    }
}
