<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Materi extends Model
{
    protected $fillable = [
        'bab_id',
        'judul',
        'isi_materi',
        'file_materi',
        'urutan',
    ];

    public function bab()
    {
        return $this->belongsTo(Bab::class);
    }

    public function getFileMateriUrlAttribute()
    {
        return $this->file_materi ? asset('storage/materi/'.$this->file_materi) : null;
    }

    public function tugas()
    {
        return $this->hasMany(Tugas::class);
    }
}
