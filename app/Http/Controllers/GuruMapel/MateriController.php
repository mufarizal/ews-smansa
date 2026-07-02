<?php

namespace App\Http\Controllers\GuruMapel;

use App\Http\Controllers\Controller;
use App\Models\Bab;
use App\Models\Materi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MateriController extends Controller
{
    public function index(Bab $bab): View
    {
        if ($bab->guruMapelKelas->guru_id !== Auth::user()->guru->id) {
            abort(403);
        }

        $bab->load(['guruMapelKelas.mapel', 'guruMapelKelas.kelas']);
        $materis = $bab->materi()->orderBy('urutan')->paginate(20);

        return view('guru_mapel.materi.index', compact('bab', 'materis'));
    }

    public function create(Bab $bab): View
    {
        if ($bab->guruMapelKelas->guru_id !== Auth::user()->guru->id) {
            abort(403);
        }

        $bab->load(['guruMapelKelas.mapel', 'guruMapelKelas.kelas']);

        return view('guru_mapel.materi.create', compact('bab'));
    }

    public function store(Bab $bab, Request $request): RedirectResponse
    {
        if ($bab->guruMapelKelas->guru_id !== Auth::user()->guru->id) {
            abort(403);
        }

        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'isi_materi' => 'nullable|string',
            'file_materi' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'urutan' => 'required|integer|min:1',
        ]);

        $validated['urutan'] = (int) $validated['urutan'];
        $validated['bab_id'] = $bab->id;

        if ($request->hasFile('file_materi')) {
            $validated['file_materi'] = $request->file('file_materi')
                ->store('public/materi');
            $validated['file_materi'] = str_replace('public/materi/', '', $validated['file_materi']);
        }

        Materi::create($validated);

        return redirect()->route('guru_mapel.bab.materi.index', $bab)
            ->with('success', 'Materi berhasil ditambahkan.');
    }

    public function edit(Bab $bab, Materi $materi): View
    {
        if ($bab->guruMapelKelas->guru_id !== Auth::user()->guru->id) {
            abort(403);
        }

        $bab->load(['guruMapelKelas.mapel', 'guruMapelKelas.kelas']);

        return view('guru_mapel.materi.edit', compact('bab', 'materi'));
    }

    public function update(Bab $bab, Request $request, Materi $materi): RedirectResponse
    {
        if ($bab->guruMapelKelas->guru_id !== Auth::user()->guru->id) {
            abort(403);
        }

        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'isi_materi' => 'nullable|string',
            'file_materi' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'urutan' => 'required|integer|min:1',
        ]);

        $validated['urutan'] = (int) $validated['urutan'];

        if ($request->hasFile('file_materi')) {
            if ($materi->file_materi) {
                Storage::delete('public/materi/'.$materi->file_materi);
            }

            $validated['file_materi'] = $request->file('file_materi')
                ->store('public/materi');
            $validated['file_materi'] = str_replace('public/materi/', '', $validated['file_materi']);
        }

        $materi->update($validated);

        return redirect()->route('guru_mapel.bab.materi.index', $bab)
            ->with('success', 'Materi berhasil diperbarui.');
    }

    public function destroy(Bab $bab, Materi $materi): RedirectResponse
    {
        if ($bab->guruMapelKelas->guru_id !== Auth::user()->guru->id) {
            abort(403);
        }

        if ($materi->file_materi) {
            Storage::delete('public/materi/'.$materi->file_materi);
        }

        $materi->delete();

        return redirect()->route('guru_mapel.bab.materi.index', $bab)
            ->with('success', 'Materi berhasil dihapus.');
    }
}