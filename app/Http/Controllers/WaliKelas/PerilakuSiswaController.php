<?php

namespace App\Http\Controllers\WaliKelas;

use App\Http\Controllers\Controller;
use App\Models\GuruMapelKelas;
use App\Models\Perilaku;
use App\Models\PerilakuSiswa;
use App\Models\Siswa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PerilakuSiswaController extends Controller
{
    private function getGuruMapelKelasIds(): array
    {
        $guru = Auth::user()->guru;
        abort_if(! $guru, 403, 'Data guru tidak ditemukan.');

        return GuruMapelKelas::where('guru_id', $guru->id)
            ->pluck('id')
            ->toArray();
    }

    public function index(): View
    {
        $guru = Auth::user()->guru;
        abort_if(! $guru, 403, 'Data guru tidak ditemukan.');

        $guruMapelKelasIds = $this->getGuruMapelKelasIds();

        $kelasIds = GuruMapelKelas::whereIn('id', $guruMapelKelasIds)
            ->distinct()
            ->pluck('kelas_id');

        $siswaList = Siswa::with('kelas')
            ->whereIn('kelas_id', $kelasIds)
            ->orderBy('nama')
            ->get();

        $perilakuList = Perilaku::where('status_aktif', true)
            ->orderBy('jenis')
            ->orderBy('nama_perilaku')
            ->get();

        $perilakuSiswas = PerilakuSiswa::with(['siswa.kelas', 'perilaku', 'guru'])
            ->where('guru_id', $guru->id)
            ->latest()
            ->paginate(20);

        return view('wali_kelas.perilaku_siswa.index', [
            'perilakuSiswas' => $perilakuSiswas,
            'siswaList' => $siswaList,
            'perilakuList' => $perilakuList,
        ]);
    }

    public function create(): View
    {
        $guru = Auth::user()->guru;
        abort_if(! $guru, 403, 'Data guru tidak ditemukan.');

        $guruMapelKelasIds = $this->getGuruMapelKelasIds();

        $kelasIds = GuruMapelKelas::whereIn('id', $guruMapelKelasIds)
            ->distinct()
            ->pluck('kelas_id');

        $siswaList = Siswa::with('kelas')
            ->whereIn('kelas_id', $kelasIds)
            ->orderBy('nama')
            ->get();

        $perilakuList = Perilaku::where('status_aktif', true)
            ->orderBy('jenis')
            ->orderBy('nama_perilaku')
            ->get();

        return view('wali_kelas.perilaku_siswa.create', [
            'siswaList' => $siswaList,
            'perilakuList' => $perilakuList,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $guru = Auth::user()->guru;
        abort_if(! $guru, 403, 'Data guru tidak ditemukan.');

        $validated = $request->validate([
            'siswa_id' => ['required', 'exists:siswas,id'],
            'perilaku_id' => ['required', 'exists:perilakus,id'],
            'tanggal' => ['required', 'date'],
            'catatan' => ['nullable', 'string'],
        ]);

        $siswa = Siswa::findOrFail($validated['siswa_id']);
        $perilaku = Perilaku::findOrFail($validated['perilaku_id']);

        $guruMapelKelasIds = $this->getGuruMapelKelasIds();

        $kelasIds = GuruMapelKelas::whereIn('id', $guruMapelKelasIds)
            ->distinct()
            ->pluck('kelas_id');

        abort_if(! in_array($siswa->kelas_id, $kelasIds->toArray()), 403);

        PerilakuSiswa::create([
            'siswa_id' => $siswa->id,
            'perilaku_id' => $perilaku->id,
            'guru_id' => $guru->id,
            'tanggal' => $validated['tanggal'],
            'catatan' => $validated['catatan'],
        ]);

        return redirect()->route('wali_kelas.perilaku-siswa.index')
            ->with('success', 'Perilaku siswa berhasil dicatat.');
    }

    public function show(PerilakuSiswa $perilakuSiswa): View
    {
        $guru = Auth::user()->guru;
        abort_if(! $guru || $perilakuSiswa->guru_id !== $guru->id, 403);

        $perilakuSiswa->load('siswa.kelas', 'perilaku', 'guru');

        return view('wali_kelas.perilaku_siswa.show', compact('perilakuSiswa'));
    }

    public function edit(PerilakuSiswa $perilakuSiswa): View
    {
        $guru = Auth::user()->guru;
        abort_if(! $guru || $perilakuSiswa->guru_id !== $guru->id, 403);

        $guruMapelKelasIds = $this->getGuruMapelKelasIds();

        $kelasIds = GuruMapelKelas::whereIn('id', $guruMapelKelasIds)
            ->distinct()
            ->pluck('kelas_id');

        $siswaList = Siswa::with('kelas')
            ->whereIn('kelas_id', $kelasIds)
            ->orderBy('nama')
            ->get();

        $perilakuList = Perilaku::where('status_aktif', true)
            ->orderBy('jenis')
            ->orderBy('nama_perilaku')
            ->get();

        return view('wali_kelas.perilaku_siswa.edit', [
            'perilakuSiswa' => $perilakuSiswa,
            'siswaList' => $siswaList,
            'perilakuList' => $perilakuList,
        ]);
    }

    public function update(Request $request, PerilakuSiswa $perilakuSiswa): RedirectResponse
    {
        $guru = Auth::user()->guru;
        abort_if(! $guru || $perilakuSiswa->guru_id !== $guru->id, 403);

        $validated = $request->validate([
            'siswa_id' => ['required', 'exists:siswas,id'],
            'perilaku_id' => ['required', 'exists:perilakus,id'],
            'tanggal' => ['required', 'date'],
            'catatan' => ['nullable', 'string'],
        ]);

        $siswa = Siswa::findOrFail($validated['siswa_id']);

        $guruMapelKelasIds = $this->getGuruMapelKelasIds();

        $kelasIds = GuruMapelKelas::whereIn('id', $guruMapelKelasIds)
            ->distinct()
            ->pluck('kelas_id');

        abort_if(! in_array($siswa->kelas_id, $kelasIds->toArray()), 403);

        $perilakuSiswa->update($validated);

        return redirect()->route('wali_kelas.perilaku-siswa.index')
            ->with('success', 'Perilaku siswa berhasil diperbarui.');
    }

    public function destroy(PerilakuSiswa $perilakuSiswa): RedirectResponse
    {
        $guru = Auth::user()->guru;
        abort_if(! $guru || $perilakuSiswa->guru_id !== $guru->id, 403);

        $perilakuSiswa->delete();

        return redirect()->route('wali_kelas.perilaku-siswa.index')
            ->with('success', 'Perilaku siswa berhasil dihapus.');
    }
}
