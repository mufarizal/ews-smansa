<?php

namespace App\Http\Controllers\GuruMapel;

use App\Http\Controllers\Controller;
use App\Models\Bab;
use App\Models\GuruMapelKelas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BabController extends Controller
{
    public function index(): View
    {
        $guru = Auth::user()->guru;
        if (! $guru) {
            return view('guru_mapel.bab.index', [
                'babs' => collect(),
                'assignments' => collect(),
            ]);
        }

        $babs = Bab::with([
            'guruMapelKelas.mapel',
            'guruMapelKelas.kelas',
        ])
            ->withCount('materi')
            ->ofGuru($guru->id)
            ->orderBy('urutan')
            ->paginate(15);

        $assignments = GuruMapelKelas::with(['mapel', 'kelas'])
            ->where('guru_id', $guru->id)
            ->get();

        return view('guru_mapel.bab.index', compact('babs', 'assignments'));
    }

    public function create(): View
    {
        $guru = Auth::user()->guru;
        if (! $guru) {
            return redirect()->route('guru_mapel.bab.index')->with('error', 'Data guru tidak ditemukan.');
        }

        $assignments = GuruMapelKelas::with(['mapel', 'kelas'])
            ->where('guru_id', $guru->id)
            ->get()
            ->groupBy(fn ($a) => $a->mapel->nama ?? 'Tanpa Mapel');

        return view('guru_mapel.bab.create', compact('assignments'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_bab' => 'required|string|max:255',
            'urutan' => 'required|integer|min:1',
            'deskripsi' => 'nullable|string',
            'guru_mapel_kelas_id' => 'required|exists:guru_mapel_kelas,id',
        ]);

        $validated['urutan'] = (int) $validated['urutan'];

        $assignment = GuruMapelKelas::with(['mapel', 'kelas'])->findOrFail($validated['guru_mapel_kelas_id']);
        if ($assignment->guru_id !== Auth::user()->guru->id) {
            abort(403);
        }

        Bab::create($validated);

        return redirect()->route('guru_mapel.bab.index')
            ->with('success', 'Bab berhasil ditambahkan.');
    }

    public function show(Bab $bab): View
    {
        if ($bab->guruMapelKelas->guru_id !== Auth::user()->guru->id) {
            abort(403);
        }

        $bab->load([
            'guruMapelKelas.mapel',
            'guruMapelKelas.kelas',
            'materi' => fn ($q) => $q->orderBy('urutan'),
        ]);

        return view('guru_mapel.bab.show', compact('bab'));
    }

    public function edit(Bab $bab): View
    {
        if ($bab->guruMapelKelas->guru_id !== Auth::user()->guru->id) {
            abort(403);
        }

        $bab->load(['guruMapelKelas.mapel', 'guruMapelKelas.kelas']);

        return view('guru_mapel.bab.edit', compact('bab'));
    }

    public function update(Request $request, Bab $bab): RedirectResponse
    {
        if ($bab->guruMapelKelas->guru_id !== Auth::user()->guru->id) {
            abort(403);
        }

        $validated = $request->validate([
            'nama_bab' => 'required|string|max:255',
            'urutan' => 'required|integer|min:1',
            'deskripsi' => 'nullable|string',
        ]);

        $validated['urutan'] = (int) $validated['urutan'];

        $bab->update($validated);

        return redirect()->route('guru_mapel.bab.show', $bab)
            ->with('success', 'Bab berhasil diperbarui.');
    }

    public function destroy(Bab $bab): RedirectResponse
    {
        if ($bab->guruMapelKelas->guru_id !== Auth::user()->guru->id) {
            abort(403);
        }

        $bab->delete();

        return redirect()->route('guru_mapel.bab.index')
            ->with('success', 'Bab berhasil dihapus.');
    }
}
