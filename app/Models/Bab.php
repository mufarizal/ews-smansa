<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bab extends Model
{
    protected $fillable = [
        'guru_mapel_kelas_id',
        'nama_bab',
        'urutan',
        'deskripsi',
    ];

    public function guruMapelKelas()
    {
        return $this->belongsTo(GuruMapelKelas::class);
    }

    public function materi()
    {
        return $this->hasMany(Materi::class);
    }

    public function scopeOfGuru($query, $guruId)
    {
        return $query->whereHas('guruMapelKelas', fn ($q) => $q->where('guru_id', $guruId));
    }
}
