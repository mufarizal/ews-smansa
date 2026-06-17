<?php

namespace App\Http\Controllers\Kurikulum;

use App\Http\Controllers\Controller;
use App\Models\Mapel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MapelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));

        $mapelsQuery = Mapel::query()
            ->orderBy('nama')
            ->select('mapels.*');

        if ($search !== '') {
            $mapelsQuery->where('mapels.nama', 'like', "%{$search}%");
        }

        $mapels = $mapelsQuery->get();

        return view('kurikulum.mapel.index', compact('mapels', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('kurikulum.mapel.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => ['required', 'string', 'max:255', Rule::unique('mapels', 'nama')],
        ]);

        Mapel::create([
            'nama' => $request->nama,
        ]);

        return redirect()->route('kurikulum.mapel.index')
            ->with('success', 'Mata Pelajaran berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Mapel $mapel)
    {
        return view('kurikulum.mapel.edit', compact('mapel'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Mapel $mapel)
    {
        $request->validate([
            'nama' => ['required', 'string', 'max:255', Rule::unique('mapels', 'nama')->ignore($mapel->id)],
        ]);

        $mapel->update([
            'nama' => $request->nama,
        ]);

        return redirect()->route('kurikulum.mapel.index')
            ->with('success', 'Mata Pelajaran berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Mapel $mapel)
    {
        $mapel->delete();
        return back()->with('success', 'Mata Pelajaran berhasil dihapus.');
    }
}
