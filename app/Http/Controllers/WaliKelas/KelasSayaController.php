<?php

namespace App\Http\Controllers\WaliKelas;

use App\Http\Controllers\Controller;

class KelasSayaController extends Controller
{
    public function index()
    {
        $guru = auth()->user()->guru;

        if (!$guru) {
            return view('wali_kelas.kelas_saya.index', [
                'kelas' => null,
                'siswa' => collect(),
                'siswaCount' => 0,
            ]);
        }

        $kelas = $guru->kelasDiampu()
            ->with(['semester'])
            ->withCount(['siswas as siswa_count'])
            ->orderBy('nama_kelas')
            ->first();

        if (!$kelas) {
            return view('wali_kelas.kelas_saya.index', [
                'kelas' => null,
                'siswa' => collect(),
                'siswaCount' => 0,
            ]);
        }

        $siswa = $kelas->siswas()
            ->with(['kelas', 'user'])
            ->orderBy('nama')
            ->orderBy('nis')
            ->paginate(25)
            ->withQueryString();

        $siswaCount = $kelas->siswa_count;

        return view('wali_kelas.kelas_saya.index', compact(
            'kelas',
            'siswa',
            'siswaCount'
        ));
    }
}
