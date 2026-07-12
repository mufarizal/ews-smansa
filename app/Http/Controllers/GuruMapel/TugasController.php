<?php

namespace App\Http\Controllers\GuruMapel;

use App\Http\Controllers\Controller;
use App\Models\GuruMapelKelas;
use App\Models\NilaiTugas;
use App\Models\SoalTugas;
use App\Models\Tugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TugasController extends Controller
{
    /**
     * Ambil ID GuruMapelKelas milik guru yang sedang login.
     */
    private function getGuruMapelKelasIds(): array
    {
        $guru = Auth::user()->guru;
        abort_if(! $guru, 403, 'Data guru tidak ditemukan.');

        return GuruMapelKelas::where('guru_id', $guru->id)
            ->pluck('id')
            ->toArray();
    }

    /**
     * Daftar semua tugas milik guru yang login.
     */
    public function index()
    {
        $ids = $this->getGuruMapelKelasIds();

        $tugas = Tugas::with(['materi.bab', 'guruMapelKelas.mapel', 'guruMapelKelas.kelas'])
            ->whereIn('guru_mapel_kelas_id', $ids)
            ->latest()
            ->get();

        return view('guru_mapel.tugas.index', compact('tugas'));
    }

    /**
     * Form tambah tugas baru.
     * Pass data GuruMapelKelas beserta bab dan materi untuk dropdown.
     */
    public function create()
    {
        $guru = Auth::user()->guru;
        abort_if(! $guru, 403, 'Data guru tidak ditemukan.');

        $guruMapelKelas = GuruMapelKelas::with(['mapel', 'kelas', 'babs.materi'])
            ->where('guru_id', $guru->id)
            ->get();

        return view('guru_mapel.tugas.create', compact('guruMapelKelas'));
    }

    /**
     * Simpan tugas baru.
     * Validasi memastikan guru_mapel_kelas_id benar-benar milik guru ini.
     */
    public function store(Request $request)
    {
        $ids = $this->getGuruMapelKelasIds();

        $data = $request->validate([
            'guru_mapel_kelas_id' => ['required', 'integer', 'in:'.implode(',', $ids)],
            'materi_id' => ['required', 'exists:materis,id'],
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'tanggal_tugas' => ['required', 'date'],
            'tanggal_deadline' => ['nullable', 'date', 'after_or_equal:tanggal_tugas'],
            'jenis' => ['required', 'in:online,offline'],
            'link_meeting' => ['nullable', 'url', 'required_if:jenis,online'],
        ]);

        Tugas::create($data);

        return redirect()->route('guru_mapel.tugas.index')
            ->with('success', 'Tugas berhasil ditambahkan.');
    }

    /**
     * Detail satu tugas beserta daftar nilai siswa.
     */
    public function show(Tugas $tugas)
    {
        $ids = $this->getGuruMapelKelasIds();

        abort_unless(in_array($tugas->guru_mapel_kelas_id, $ids), 403);

        $tugas->load([
            'materi.bab',
            'guruMapelKelas.mapel',
            'guruMapelKelas.kelas',
            'nilaiTugas.siswa',
            'soalTugas',
        ]);

        return view('guru_mapel.tugas.show', compact('tugas'));
    }

    /**
     * Form edit tugas.
     * Pass data GuruMapelKelas beserta bab dan materi untuk dropdown,
     * sama seperti create agar dropdown tetap tampil.
     */
    public function edit(Tugas $tugas)
    {
        $ids = $this->getGuruMapelKelasIds();
        $guru = Auth::user()->guru;
        abort_if(! $guru, 403, 'Data guru tidak ditemukan.');

        abort_unless(in_array($tugas->guru_mapel_kelas_id, $ids), 403);

        $guruMapelKelas = GuruMapelKelas::with(['mapel', 'kelas', 'babs.materi'])
            ->where('guru_id', $guru->id)
            ->get();

        return view('guru_mapel.tugas.edit', compact('tugas', 'guruMapelKelas'));
    }

    /**
     * Update data tugas.
     */
    public function update(Request $request, Tugas $tugas)
    {
        $ids = $this->getGuruMapelKelasIds();

        abort_unless(in_array($tugas->guru_mapel_kelas_id, $ids), 403);

        $data = $request->validate([
            'materi_id' => ['required', 'exists:materis,id'],
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'tanggal_tugas' => ['required', 'date'],
            'tanggal_deadline' => ['nullable', 'date', 'after_or_equal:tanggal_tugas'],
            'jenis' => ['required', 'in:online,offline'],
            'link_meeting' => ['nullable', 'url', 'required_if:jenis,online'],
        ]);

        $tugas->update($data);

        return redirect()->route('guru_mapel.tugas.show', $tugas)
            ->with('success', 'Tugas berhasil diperbarui.');
    }

    /**
     * Hapus tugas. NilaiTugas terhapus otomatis via cascadeOnDelete.
     */
    public function destroy(Tugas $tugas)
    {
        $ids = $this->getGuruMapelKelasIds();

        abort_unless(in_array($tugas->guru_mapel_kelas_id, $ids), 403);

        $tugas->delete();

        return redirect()->route('guru_mapel.tugas.index')
            ->with('success', 'Tugas berhasil dihapus.');
    }

    /**
     * Halaman penilaian: daftar siswa beserta nilai mereka untuk satu tugas.
     */
    public function nilaiIndex(Tugas $tugas)
    {
        $ids = $this->getGuruMapelKelasIds();

        abort_unless(in_array($tugas->guru_mapel_kelas_id, $ids), 403);

        $nilaiTugas = NilaiTugas::with('siswa')
            ->where('tugas_id', $tugas->id)
            ->get();

        $tugas->load('materi');

        return view('guru_mapel.tugas.nilai', compact('tugas', 'nilaiTugas'));
    }

    /**
     * Guru mengisi atau update nilai satu siswa untuk satu tugas.
     * Menggunakan updateOrCreate agar bisa dipanggil berkali-kali (idempoten).
     */
    public function nilaiStore(Request $request, Tugas $tugas)
    {
        $ids = $this->getGuruMapelKelasIds();

        abort_unless(in_array($tugas->guru_mapel_kelas_id, $ids), 403);

        $data = $request->validate([
            'siswa_id' => ['required', 'exists:siswas,id'],
            'nilai' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status' => ['required', 'in:mengerjakan,tidak_mengerjakan,selesai'],
            'catatan' => ['nullable', 'string'],
        ]);

        NilaiTugas::updateOrCreate(
            ['tugas_id' => $tugas->id, 'siswa_id' => $data['siswa_id']],
            [
                'nilai' => $data['nilai'] ?? null,
                'status' => $data['status'],
                'catatan' => $data['catatan'] ?? null,
            ]
        );

        return redirect()->route('guru_mapel.tugas.nilai.index', $tugas)
            ->with('success', 'Nilai berhasil disimpan.');
    }

    /**
     * Tambah soal baru ke dalam tugas (untuk tugas online).
     */
    public function soalStore(Request $request, Tugas $tugas)
    {
        $ids = $this->getGuruMapelKelasIds();

        abort_unless(in_array($tugas->guru_mapel_kelas_id, $ids), 403);
        abort_if($tugas->jenis !== 'online', 422, 'Hanya tugas online yang dapat menambahkan soal.');

        $data = $request->validate([
            'soal' => ['required', 'string'],
            'opsi_a' => ['required', 'string'],
            'opsi_b' => ['required', 'string'],
            'opsi_c' => ['required', 'string'],
            'opsi_d' => ['required', 'string'],
            'jawaban_benar' => ['required', 'in:a,b,c,d'],
            'bobot' => ['required', 'integer', 'min:1'],
        ]);

        $tugas->soalTugas()->create($data);

        return redirect()->route('guru_mapel.tugas.show', $tugas)
            ->with('success', 'Soal berhasil ditambahkan.');
    }

    /**
     * Update satu soal tugas.
     */
    public function soalUpdate(Request $request, Tugas $tugas, SoalTugas $soal)
    {
        $ids = $this->getGuruMapelKelasIds();

        abort_unless(in_array($tugas->guru_mapel_kelas_id, $ids), 403);
        abort_unless($soal->tugas_id === $tugas->id, 404);
        abort_if($tugas->jenis !== 'online', 422, 'Hanya tugas online yang dapat mengubah soal.');

        $data = $request->validate([
            'soal' => ['required', 'string'],
            'opsi_a' => ['required', 'string'],
            'opsi_b' => ['required', 'string'],
            'opsi_c' => ['required', 'string'],
            'opsi_d' => ['required', 'string'],
            'jawaban_benar' => ['required', 'in:a,b,c,d'],
            'bobot' => ['required', 'integer', 'min:1'],
        ]);

        $soal->update($data);

        return redirect()->route('guru_mapel.tugas.show', $tugas)
            ->with('success', 'Soal berhasil diperbarui.');
    }

    /**
     * Form edit soal tugas.
     */
    public function soalEdit(Tugas $tugas, SoalTugas $soal)
    {
        $ids = $this->getGuruMapelKelasIds();

        abort_unless(in_array($tugas->guru_mapel_kelas_id, $ids), 403);
        abort_unless($soal->tugas_id === $tugas->id, 404);
        abort_if($tugas->jenis !== 'online', 422, 'Hanya tugas online yang dapat mengubah soal.');

        $tugas->load('materi');

        return view('guru_mapel.tugas.soal.edit', compact('tugas', 'soal'));
    }

    /**
     * Hapus satu soal tugas.
     */
    public function soalDestroy(Tugas $tugas, SoalTugas $soal)
    {
        $ids = $this->getGuruMapelKelasIds();

        abort_unless(in_array($tugas->guru_mapel_kelas_id, $ids), 403);
        abort_unless($soal->tugas_id === $tugas->id, 404);

        $soal->delete();

        return redirect()->route('guru_mapel.tugas.show', $tugas)
            ->with('success', 'Soal berhasil dihapus.');
    }
}
