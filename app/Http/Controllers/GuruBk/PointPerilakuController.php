<?php

namespace App\Http\Controllers\GuruBk;

use App\Http\Controllers\Controller;
use App\Models\Perilaku;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PointPerilakuController extends Controller
{
    public function index(): View
    {
        $perilakus = Perilaku::orderBy('jenis')
            ->orderBy('nama_perilaku')
            ->get();

        return view('guru_bk.point-perilaku.index', compact('perilakus'));
    }

    public function create(): View
    {
        return view('guru_bk.point-perilaku.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_perilaku' => ['required', 'string', 'max:255'],
            'jenis' => ['required', 'in:positif,negatif'],
            'poin' => ['required', 'integer'],
            'status_aktif' => ['sometimes', 'boolean'],
        ]);

        $validated['status_aktif'] = $request->has('status_aktif');

        Perilaku::create($validated);

        return redirect()->route('guru_bk.point-perilaku.index')
            ->with('success', 'Point perilaku berhasil ditambahkan.');
    }

    public function edit(Perilaku $perilaku): View
    {
        return view('guru_bk.point-perilaku.edit', compact('perilaku'));
    }

    public function show(Perilaku $perilaku): RedirectResponse
    {
        return redirect()->route('guru_bk.point-perilaku.edit', $perilaku);
    }

    public function update(Request $request, Perilaku $perilaku): RedirectResponse
    {
        $validated = $request->validate([
            'nama_perilaku' => ['required', 'string', 'max:255'],
            'jenis' => ['required', 'in:positif,negatif'],
            'poin' => ['required', 'integer'],
            'status_aktif' => ['sometimes', 'boolean'],
        ]);

        $validated['status_aktif'] = $request->has('status_aktif');

        $perilaku->update($validated);

        return redirect()->route('guru_bk.point-perilaku.index')
            ->with('success', 'Point perilaku berhasil diperbarui.');
    }

    public function destroy(Perilaku $perilaku): RedirectResponse
    {
        $perilaku->delete();

        return redirect()->route('guru_bk.point-perilaku.index')
            ->with('success', 'Point perilaku berhasil dihapus.');
    }
}
