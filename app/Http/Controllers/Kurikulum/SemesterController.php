<?php

namespace App\Http\Controllers\Kurikulum;

use App\Http\Controllers\Controller;
use App\Models\Semester;
use Illuminate\Http\Request;

class SemesterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $semesters = Semester::orderBy('tanggal_mulai', 'desc')->get();
        return view('kurikulum.semesters.index', compact('semesters'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('kurikulum.semesters.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
            'is_active' => 'boolean',
            'keterangan' => 'nullable'
        ]);

        $data['is_active'] = $request->boolean('is_active');
        Semester::create($data);

        return redirect()->route('kurikulum.semesters.index')->with('success', 'Semester berhasil ditambahkan.');
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
    public function edit(Semester $semester)
    {
        return view('kurikulum.semesters.edit', compact('semester'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Semester $semester)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
            'keterangan' => 'nullable|string',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        if ($data['is_active']) {
            Semester::query()->update([
                'is_active' => false
            ]);
        }

        $semester->update($data);

        return redirect()->route('kurikulum.semesters.index')->with('success', 'Semester berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Semester $semester)
    {
        if ($semester->is_active) {
            return back()->with('error', 'Semester aktif tidak dapat dihapus.');
        }
        $semester->delete();
        return redirect()->route('kurikulum.semesters.index')->with('success', 'Semester berhasil dihapus.');
    }
}
