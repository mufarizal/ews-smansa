<?php

namespace App\Http\Controllers\GuruMapel;

use App\Http\Controllers\Controller;
use App\Models\GuruMapelKelas;
use App\Models\SoalUjian;
use App\Models\UjianHarian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UjianController extends Controller
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
     * Daftar semua ujian milik guru yang login.
     */
    public function index()
    {
        $ids = $this->getGuruMapelKelasIds();

        $ujians = UjianHarian::with(['bab', 'guruMapelKelas.mapel', 'guruMapelKelas.kelas'])
            ->whereIn('guru_mapel_kelas_id', $ids)
            ->latest()
            ->get();

        return view('guru_mapel.ujian.index', compact('ujians'));
    }

    /**
     * Form tambah ujian baru.
     * Pass data GuruMapelKelas beserta bab untuk dropdown.
     */
    public function create()
    {
        $guru = Auth::user()->guru;
        abort_if(! $guru, 403, 'Data guru tidak ditemukan.');

        $guruMapelKelas = GuruMapelKelas::with(['mapel', 'kelas', 'babs'])
            ->where('guru_id', $guru->id)
            ->get();

        return view('guru_mapel.ujian.create', compact('guruMapelKelas'));
    }

    /**
     * Simpan ujian baru dengan status awal 'draft'.
     */
    public function store(Request $request)
    {
        $ids = $this->getGuruMapelKelasIds();

        $data = $request->validate([
            'guru_mapel_kelas_id' => ['required', 'integer', 'in:'.implode(',', $ids)],
            'bab_id' => ['required', 'exists:babs,id'],
            'judul' => ['required', 'string', 'max:255'],
            'tanggal_ujian' => ['required', 'date'],
            'durasi_menit' => ['required', 'integer', 'min:1'],
        ]);

        $data['status'] = 'draft';

        $ujian = UjianHarian::create($data);

        return redirect()->route('guru_mapel.ujian.show', $ujian)
            ->with('success', 'Ujian berhasil dibuat. Silakan tambahkan soal.');
    }

    /**
     * Detail ujian beserta semua soal-soalnya.
     */
    public function show(UjianHarian $ujianHarian)
    {
        $ids = $this->getGuruMapelKelasIds();

        abort_unless(in_array($ujianHarian->guru_mapel_kelas_id, $ids), 403);

        $ujianHarian->load([
            'bab',
            'guruMapelKelas.mapel',
            'guruMapelKelas.kelas',
            'soalUjians',
        ]);

        return view('guru_mapel.ujian.show', compact('ujianHarian'));
    }

    /**
     * Form edit ujian.
     * Pass data GuruMapelKelas beserta bab untuk dropdown,
     * sama seperti create agar dropdown tetap tampil.
     */
    public function edit(UjianHarian $ujianHarian)
    {
        $ids = $this->getGuruMapelKelasIds();
        $guru = Auth::user()->guru;
        abort_if(! $guru, 403, 'Data guru tidak ditemukan.');

        abort_unless(in_array($ujianHarian->guru_mapel_kelas_id, $ids), 403);
        abort_if($ujianHarian->status !== 'draft', 422, 'Ujian yang sudah dipublish tidak dapat diubah.');

        $guruMapelKelas = GuruMapelKelas::with(['mapel', 'kelas', 'babs'])
            ->where('guru_id', $guru->id)
            ->get();

        return view('guru_mapel.ujian.edit', compact('ujianHarian', 'guruMapelKelas'));
    }

    /**
     * Update data ujian. Hanya bisa diubah jika masih berstatus 'draft'.
     */
    public function update(Request $request, UjianHarian $ujianHarian)
    {
        $ids = $this->getGuruMapelKelasIds();

        abort_unless(in_array($ujianHarian->guru_mapel_kelas_id, $ids), 403);
        abort_if($ujianHarian->status !== 'draft', 422, 'Ujian yang sudah dipublish tidak dapat diubah.');

        $data = $request->validate([
            'bab_id' => ['required', 'exists:babs,id'],
            'judul' => ['required', 'string', 'max:255'],
            'tanggal_ujian' => ['required', 'date'],
            'durasi_menit' => ['required', 'integer', 'min:1'],
        ]);

        $ujianHarian->update($data);

        return redirect()->route('guru_mapel.ujian.show', $ujianHarian)
            ->with('success', 'Ujian berhasil diperbarui.');
    }

    /**
     * Hapus ujian. Soal, jawaban, dan hasil terhapus via cascadeOnDelete.
     * Hanya bisa dihapus jika masih berstatus 'draft'.
     */
    public function destroy(UjianHarian $ujianHarian)
    {
        $ids = $this->getGuruMapelKelasIds();

        abort_unless(in_array($ujianHarian->guru_mapel_kelas_id, $ids), 403);
        abort_if($ujianHarian->status !== 'draft', 422, 'Ujian yang sudah dipublish tidak dapat dihapus.');

        $ujianHarian->delete();

        return redirect()->route('guru_mapel.ujian.index')
            ->with('success', 'Ujian berhasil dihapus.');
    }

    /**
     * Publish ujian: ubah status dari 'draft' ke 'publish'.
     * Validasi: ujian harus punya minimal 1 soal sebelum bisa dipublish.
     */
    public function publish(UjianHarian $ujianHarian)
    {
        $ids = $this->getGuruMapelKelasIds();

        abort_unless(in_array($ujianHarian->guru_mapel_kelas_id, $ids), 403);
        abort_if($ujianHarian->status !== 'draft', 422, 'Ujian sudah pernah dipublish.');
        abort_if($ujianHarian->soalUjians()->count() === 0, 422, 'Ujian harus memiliki minimal 1 soal sebelum dipublish.');

        $ujianHarian->update(['status' => 'publish']);

        return redirect()->route('guru_mapel.ujian.show', $ujianHarian)
            ->with('success', 'Ujian berhasil dipublish.');
    }

    // -----------------------------------------------------------------------
    // SOAL UJIAN
    // -----------------------------------------------------------------------

    /**
     * Tambah soal baru ke dalam ujian.
     * Ujian harus berstatus 'draft' agar soal masih bisa ditambahkan.
     * Setelah berhasil, kembali ke halaman detail ujian.
     */
    public function soalStore(Request $request, UjianHarian $ujianHarian)
    {
        $ids = $this->getGuruMapelKelasIds();

        abort_unless(in_array($ujianHarian->guru_mapel_kelas_id, $ids), 403);
        abort_if($ujianHarian->status !== 'draft', 422, 'Soal tidak dapat ditambahkan ke ujian yang sudah dipublish.');

        $data = $request->validate([
            'soal' => ['required', 'string'],
            'opsi_a' => ['required', 'string'],
            'opsi_b' => ['required', 'string'],
            'opsi_c' => ['required', 'string'],
            'opsi_d' => ['required', 'string'],
            'jawaban_benar' => ['required', 'in:a,b,c,d'],
            'bobot' => ['required', 'integer', 'min:1'],
        ]);

        $data['ujian_harian_id'] = $ujianHarian->id;

        SoalUjian::create($data);

        return redirect()->route('guru_mapel.ujian.show', $ujianHarian)
            ->with('success', 'Soal berhasil ditambahkan.');
    }

    public function soalUpdate(Request $request, UjianHarian $ujianHarian, SoalUjian $soal)
    {
        $ids = $this->getGuruMapelKelasIds();

        abort_unless(in_array($ujianHarian->guru_mapel_kelas_id, $ids), 403);
        abort_unless($soal->ujian_harian_id === $ujianHarian->id, 404);
        abort_if($ujianHarian->status !== 'draft', 422, 'Soal tidak dapat diubah setelah ujian dipublish.');

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

        return redirect()->route('guru_mapel.ujian.show', $ujianHarian)
            ->with('success', 'Soal berhasil diperbarui.');
    }

    public function soalEdit(UjianHarian $ujianHarian, SoalUjian $soal)
    {
        $ids = $this->getGuruMapelKelasIds();

        abort_unless(in_array($ujianHarian->guru_mapel_kelas_id, $ids), 403);
        abort_unless($soal->ujian_harian_id === $ujianHarian->id, 404);
        abort_if($ujianHarian->status !== 'draft', 422, 'Soal tidak dapat diubah setelah ujian dipublish.');

        $ujianHarian->load('bab');

        return view('guru_mapel.ujian.soal.edit', compact('ujianHarian', 'soal'));
    }

    public function soalDestroy(UjianHarian $ujianHarian, SoalUjian $soal)
    {
        $ids = $this->getGuruMapelKelasIds();

        abort_unless(in_array($ujianHarian->guru_mapel_kelas_id, $ids), 403);
        abort_unless($soal->ujian_harian_id === $ujianHarian->id, 404);
        abort_if($ujianHarian->status !== 'draft', 422, 'Soal tidak dapat dihapus setelah ujian dipublish.');

        $soal->delete();

        return redirect()->route('guru_mapel.ujian.show', $ujianHarian)
            ->with('success', 'Soal berhasil dihapus.');
    }

    /**
     * Rekap hasil ujian semua siswa untuk satu ujian.
     */
    public function hasilIndex(UjianHarian $ujianHarian)
    {
        $ids = $this->getGuruMapelKelasIds();

        abort_unless(in_array($ujianHarian->guru_mapel_kelas_id, $ids), 403);

        $ujianHarian->load(['bab', 'guruMapelKelas.mapel', 'guruMapelKelas.kelas']);

        $hasil = $ujianHarian->hasilUjians()->with('siswa')->get();

        return view('guru_mapel.ujian.hasil', compact('ujianHarian', 'hasil'));
    }
}
