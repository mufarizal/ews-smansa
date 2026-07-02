<?php

namespace App\Http\Controllers\Kurikulum;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Kelas;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));

        $kelasQuery = Kelas::with(['waliKelas'])
            ->withCount(['siswas as siswa_count'])
            ->latest();

        if ($search !== '') {
            $kelasQuery->where(function ($query) use ($search) {
                $query->where('nama_kelas', 'like', "%{$search}%")
                    ->orWhereHas('waliKelas', function ($waliQuery) use ($search) {
                        $waliQuery->where('nama', 'like', "%{$search}%")
                            ->orWhere('nip', 'like', "%{$search}%");
                    });
            });
        }

        $kelas = $kelasQuery->get();

        return view('kurikulum.kelas.index', compact(
            'kelas',
            'search',
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $gurus = Guru::whereHas('user.roles', function ($q) {
            $q->where('slug', 'guru')
                ->orWhereIn('slug', [
                    'guru_mapel',
                    'wali_kelas',
                    'guru_bk',
                    'guru_piket',
                ]);
        })->orderBy('nama')->get();

        return view('kurikulum.kelas.create', compact('gurus'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_kelas' => 'required|string|max:255',
            'wali_kelas_id' => 'nullable|exists:gurus,id',
        ]);

        Kelas::create([
            'nama_kelas' => $request->nama_kelas,
            'wali_kelas_id' => $request->wali_kelas_id,
        ]);

        return redirect()->route('kurikulum.kelas.index')->with('success', 'Kelas Berhasil Ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Kelas $kelas)
    {
        // Show all gurus that have guru_mapel or wali_kelas role
        $gurus = Guru::whereHas('user.roles', function ($q) {
            $q->whereIn('slug', ['guru_mapel', 'wali_kelas']);
        })->orderBy('nama')->get();

        return view('kurikulum.kelas.edit', compact('kelas', 'gurus'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Kelas $kelas)
    {
        $request->validate([
            'nama_kelas' => 'required|string|max:255',
            'wali_kelas_id' => 'nullable|exists:gurus,id',
        ]);

        $kelas->update([
            'nama_kelas' => $request->nama_kelas,
            'wali_kelas_id' => $request->wali_kelas_id,
        ]);

        return redirect()->route('kurikulum.kelas.index')->with('success', 'Kelas Berhasil Diperbarui.');
    }

    /**
     * Get kelas by angkatan (for cascading select)
     */

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Kelas $kelas)
    {
        $kelas->delete();

        return redirect()->route('kurikulum.kelas.index')->with('success', 'Kelas Berhasil Dihapus.');
    }
}
