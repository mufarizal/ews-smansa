<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuruBkKelas extends Model
{
    protected $fillable = [
        'guru_id',
        'semester_id',
        'kelas_id',

    ];

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }
}
