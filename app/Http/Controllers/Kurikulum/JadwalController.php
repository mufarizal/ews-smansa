<?php

namespace App\Http\Controllers\Kurikulum;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\GuruMapelKelas;
use App\Models\Jadwal;
use App\Models\JadwalKegiatan;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Semester;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class JadwalController extends Controller
{
    private const HARI_OPTIONS = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

    private const MINGGU_KE_OPTIONS = [
        1 => 'Minggu ke-1',
        2 => 'Minggu ke-2',
        3 => 'Minggu ke-3',
        4 => 'Minggu ke-4',
    ];

    // ============ Index ============

    public function index(Request $request)
    {
        $type = $request->get('type', 'pelajaran'); // 'pelajaran' | 'kegiatan'
        $activeSemester = $this->getActiveSemester();
        $semesterId = $request->filled('semester_id') ? (int) $request->semester_id : $activeSemester?->id;
        $todayHari = Jadwal::carbonToHari(now());

        // Widget hari ini — selalu tampil di kedua tab
        $jadwalHariIni = collect();
        $kegiatanHariIni = null;

        if ($activeSemester) {
            $jadwalHariIni = Jadwal::with(['kelas', 'guru', 'mapel'])
                ->where('semester_id', $activeSemester->id)
                ->where('is_active', true)
                ->where('hari', $todayHari)
                ->orderBy('jam_mulai')
                ->get();

            $kegiatanHariIni = JadwalKegiatan::forDate(Carbon::today());
        }

        if ($type === 'kegiatan') {
            $selectedHari = $request->filled('hari') ? (string) $request->hari : null;

            $kegiatans = JadwalKegiatan::with('semester')
                ->when($semesterId, fn($q) => $q->where('semester_id', $semesterId))
                ->when($selectedHari, fn($q) => $q->where('hari', $selectedHari))
                ->orderByRaw("CASE hari
                    WHEN 'Senin'  THEN 1 WHEN 'Selasa' THEN 2 WHEN 'Rabu'   THEN 3
                    WHEN 'Kamis'  THEN 4 WHEN 'Jumat'  THEN 5 WHEN 'Sabtu'  THEN 6
                    ELSE 99 END")
                ->orderBy('minggu_ke')
                ->orderBy('jam_mulai')
                ->get();

            $kegiatanPerHari = $kegiatans->groupBy('hari');

            return view('kurikulum.jadwal.index', [
                'type' => 'kegiatan',
                'kegiatanPerHari' => $kegiatanPerHari,
                'jadwalHariIni' => $jadwalHariIni,
                'kegiatanHariIni' => $kegiatanHariIni,
                'semesterList' => Semester::orderByDesc('is_active')->orderByDesc('id')->get(),
                'selectedSemester' => $semesterId,
                'activeSemester' => $activeSemester,
                'hariOptions' => self::HARI_OPTIONS,
                'selectedHari' => $selectedHari,
                'todayHari' => $todayHari,
            ]);
        }

        // type = pelajaran (default)
        $selectedKelasId = $request->filled('kelas_id') ? (int) $request->kelas_id : null;
        $selectedHari = $request->filled('hari') ? (string) $request->hari : null;
        $selectedMapelId = $request->filled('mapel_id') ? (int) $request->mapel_id : null;
        $selectedGuruId = $request->filled('guru_id') ? (int) $request->guru_id : null;
        $search = trim((string) $request->get('search', ''));

        $jadwals = Jadwal::with(['semester', 'kelas', 'guru', 'mapel'])
            ->when($semesterId, fn($q) => $q->where('semester_id', $semesterId))
            ->when($selectedKelasId, fn($q) => $q->where('kelas_id', $selectedKelasId))
            ->when($selectedHari, fn($q) => $q->where('hari', $selectedHari))
            ->when($selectedMapelId, fn($q) => $q->where('mapel_id', $selectedMapelId))
            ->when($selectedGuruId, fn($q) => $q->where('guru_id', $selectedGuruId))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($b) use ($search) {
                    $b->whereHas('kelas', fn($kq) => $kq->where('nama_kelas', 'like', "%{$search}%"))
                        ->orWhereHas('mapel', fn($mq) => $mq->where('nama', 'like', "%{$search}%"))
                        ->orWhereHas('guru', fn($gq) => $gq->where('nama', 'like', "%{$search}%")
                            ->orWhere('nip', 'like', "%{$search}%"));
                });
            })
            ->orderByRaw("CASE hari
                WHEN 'Senin'  THEN 1 WHEN 'Selasa' THEN 2 WHEN 'Rabu'   THEN 3
                WHEN 'Kamis'  THEN 4 WHEN 'Jumat'  THEN 5 WHEN 'Sabtu'  THEN 6
                ELSE 99 END")
            ->orderBy('jam_mulai')
            ->get();

        $jadwalPerHari = $jadwals->groupBy('hari');

        return view('kurikulum.jadwal.index', [
            'type' => 'pelajaran',
            'jadwalPerHari' => $jadwalPerHari,
            'jadwalHariIni' => $jadwalHariIni,
            'kegiatanHariIni' => $kegiatanHariIni,
            'semesterList' => Semester::orderByDesc('is_active')->orderByDesc('id')->get(),
            'selectedSemester' => $semesterId,
            'activeSemester' => $activeSemester,
            'mapelList' => Mapel::orderBy('nama')->get(),
            'guruList' => Guru::orderBy('nama')->get(),
            'kelasList' => Kelas::orderBy('nama_kelas')->get(),
            'hariOptions' => self::HARI_OPTIONS,
            'selectedKelasId' => $selectedKelasId,
            'selectedHari' => $selectedHari,
            'selectedMapelId' => $selectedMapelId,
            'selectedGuruId' => $selectedGuruId,
            'search' => $search,
            'todayHari' => $todayHari,
        ]);
    }

    // ============ Create ============

    public function create(Request $request)
    {
        $type = $request->get('type', 'pelajaran');
        $activeSemester = $this->getActiveSemester();

        return view('kurikulum.jadwal.create', [
            'type' => $type,
            'kelas' => Kelas::orderBy('nama_kelas')->get(),
            'guru' => Guru::orderBy('nama')->get(),
            'mapel' => Mapel::orderBy('nama')->get(),
            'semesterList' => Semester::orderByDesc('is_active')->orderByDesc('id')->get(),
            'activeSemester' => $activeSemester,
            'hariOptions' => self::HARI_OPTIONS,
            'mingguKeOptions' => self::MINGGU_KE_OPTIONS,
            'guruAssignments' => $this->buildGuruAssignments($activeSemester?->id),
        ]);
    }

    // ============ Store ============

    public function store(Request $request)
    {
        if ($request->get('type') === 'kegiatan') {
            return $this->storeKegiatan($request);
        }

        return $this->storePelajaran($request);
    }

    // ============ Edit ============

    public function edit(Request $request, $id)
    {
        $type = $request->get('type', 'pelajaran');

        if ($type === 'kegiatan') {
            $kegiatan = JadwalKegiatan::findOrFail($id);

            return view('kurikulum.jadwal.edit', [
                'type' => 'kegiatan',
                'kegiatan' => $kegiatan,
                'semesterList' => Semester::orderByDesc('is_active')->orderByDesc('id')->get(),
                'activeSemester' => $this->getActiveSemester(),
                'hariOptions' => self::HARI_OPTIONS,
                'mingguKeOptions' => self::MINGGU_KE_OPTIONS,
            ]);
        }

        $jadwal = Jadwal::findOrFail($id);

        return view('kurikulum.jadwal.edit', [
            'type' => 'pelajaran',
            'jadwal' => $jadwal,
            'kelas' => Kelas::orderBy('nama_kelas')->get(),
            'guru' => Guru::orderBy('nama')->get(),
            'mapel' => Mapel::orderBy('nama')->get(),
            'semesterList' => Semester::orderByDesc('is_active')->orderByDesc('id')->get(),
            'activeSemester' => $this->getActiveSemester(),
            'hariOptions' => self::HARI_OPTIONS,
            'guruAssignments' => $this->buildGuruAssignments($jadwal->semester_id),
        ]);
    }

    // ============ Update ============

    public function update(Request $request, $id)
    {
        if ($request->get('type') === 'kegiatan') {
            return $this->updateKegiatan($request, JadwalKegiatan::findOrFail($id));
        }

        return $this->updatePelajaran($request, Jadwal::findOrFail($id));
    }

    // ============ Destroy ============

    public function destroy(Request $request, $id)
    {
        $type = $request->get('type', 'pelajaran');

        if ($type === 'kegiatan') {
            JadwalKegiatan::findOrFail($id)->delete();
        } else {
            Jadwal::findOrFail($id)->delete();
        }

        return back()->with('success', 'Jadwal berhasil dihapus.');
    }

    // ============ Store Helpers ============

    private function storePelajaran(Request $request)
    {
        $validated = $request->validate([
            'semester_id' => 'required|exists:semesters,id',
            'kelas_id' => 'required|exists:kelas,id',
            'guru_id' => 'required|exists:gurus,id',
            'mapel_id' => 'required|exists:mapels,id',
            'hari' => 'required|string|in:' . implode(',', self::HARI_OPTIONS),
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'is_active' => 'boolean',
            'catatan' => 'nullable|string|max:500',
        ]);

        $this->ensureGuruMatchesMapelKelas(
            $validated['kelas_id'],
            $validated['mapel_id'],
            $validated['guru_id'],
            (int) $validated['semester_id']
        );

        $this->ensureNoJadwalConflict(
            semesterId: (int) $validated['semester_id'],
            kelasId: (int) $validated['kelas_id'],
            guruId: (int) $validated['guru_id'],
            hari: $validated['hari'],
            jamMulai: $validated['jam_mulai'],
            jamSelesai: $validated['jam_selesai'],
        );

        Jadwal::create($validated);

        return redirect()
            ->route('kurikulum.jadwal.index', ['type' => 'pelajaran'])
            ->with('success', 'Jadwal pelajaran berhasil ditambahkan.');
    }

    private function storeKegiatan(Request $request)
    {
        $validated = $request->validate([
            'semester_id' => 'required|exists:semesters,id',
            'hari' => 'required|string|in:' . implode(',', self::HARI_OPTIONS),
            'minggu_ke' => 'required|integer|in:1,2,3,4',
            'nama_kegiatan' => 'required|string|max:100',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'is_active' => 'boolean',
            'catatan' => 'nullable|string|max:500',
        ]);

        $this->ensureNoDuplicateKegiatan(
            semesterId: (int) $validated['semester_id'],
            hari: $validated['hari'],
            mingguKe: (int) $validated['minggu_ke'],
        );

        $this->ensureKegiatanSelesaiSebelumMapel(
            semesterId: (int) $validated['semester_id'],
            hari: $validated['hari'],
            jamSelesai: $validated['jam_selesai'],
        );

        JadwalKegiatan::create($validated);

        return redirect()
            ->route('kurikulum.jadwal.index', ['type' => 'kegiatan'])
            ->with('success', 'Jadwal kegiatan berhasil ditambahkan.');
    }

    // ============ Update Helpers ============

    private function updatePelajaran(Request $request, Jadwal $jadwal)
    {
        $validated = $request->validate([
            'semester_id' => 'required|exists:semesters,id',
            'kelas_id' => 'required|exists:kelas,id',
            'guru_id' => 'required|exists:gurus,id',
            'mapel_id' => 'required|exists:mapels,id',
            'hari' => 'required|string|in:' . implode(',', self::HARI_OPTIONS),
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'is_active' => 'boolean',
            'catatan' => 'nullable|string|max:500',
        ]);

        $this->ensureGuruMatchesMapelKelas(
            $validated['kelas_id'],
            $validated['mapel_id'],
            $validated['guru_id'],
            (int) $validated['semester_id']
        );

        $this->ensureNoJadwalConflict(
            semesterId: (int) $validated['semester_id'],
            kelasId: (int) $validated['kelas_id'],
            guruId: (int) $validated['guru_id'],
            hari: $validated['hari'],
            jamMulai: $validated['jam_mulai'],
            jamSelesai: $validated['jam_selesai'],
            ignoreId: $jadwal->id,
        );

        $jadwal->update($validated);

        return redirect()
            ->route('kurikulum.jadwal.index', ['type' => 'pelajaran'])
            ->with('success', 'Jadwal pelajaran berhasil diperbarui.');
    }

    private function updateKegiatan(Request $request, JadwalKegiatan $kegiatan)
    {
        $validated = $request->validate([
            'semester_id' => 'required|exists:semesters,id',
            'hari' => 'required|string|in:' . implode(',', self::HARI_OPTIONS),
            'minggu_ke' => 'required|integer|in:1,2,3,4',
            'nama_kegiatan' => 'required|string|max:100',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'is_active' => 'boolean',
            'catatan' => 'nullable|string|max:500',
        ]);

        $this->ensureNoDuplicateKegiatan(
            semesterId: (int) $validated['semester_id'],
            hari: $validated['hari'],
            mingguKe: (int) $validated['minggu_ke'],
            ignoreId: $kegiatan->id,
        );

        $this->ensureKegiatanSelesaiSebelumMapel(
            semesterId: (int) $validated['semester_id'],
            hari: $validated['hari'],
            jamSelesai: $validated['jam_selesai'],
        );

        $kegiatan->update($validated);

        return redirect()
            ->route('kurikulum.jadwal.index', ['type' => 'kegiatan'])
            ->with('success', 'Jadwal kegiatan berhasil diperbarui.');
    }

    // ============ Private Helpers ============

    private function getActiveSemester(): ?Semester
    {
        return Semester::where('is_active', true)->first();
    }

    private function buildGuruAssignments(?int $semesterId): \Illuminate\Support\Collection
    {
        return GuruMapelKelas::with('guru:id,nama')
            ->when($semesterId, fn($q) => $q->where('semester_id', $semesterId))
            ->get()
            ->map(fn(GuruMapelKelas $item) => [
                'kelas_id' => $item->kelas_id,
                'mapel_id' => $item->mapel_id,
                'guru_id' => $item->guru_id,
                'guru_nama' => $item->guru?->nama ?? '-',
            ])
            ->values();
    }

    private function ensureGuruMatchesMapelKelas(int $kelasId, int $mapelId, int $guruId, int $semesterId): void
    {
        $exists = GuruMapelKelas::where('kelas_id', $kelasId)
            ->where('mapel_id', $mapelId)
            ->where('guru_id', $guruId)
            ->where('semester_id', $semesterId)
            ->exists();

        if (!$exists) {
            throw ValidationException::withMessages([
                'guru_id' => 'Guru tidak terdaftar mengajar mapel ini di kelas yang dipilih pada semester tersebut.',
            ]);
        }
    }

    private function ensureNoJadwalConflict(
        int $semesterId,
        int $kelasId,
        int $guruId,
        string $hari,
        string $jamMulai,
        string $jamSelesai,
        ?int $ignoreId = null
    ): void {
        $base = Jadwal::query()
            ->where('semester_id', $semesterId)
            ->where('hari', $hari)
            ->where('jam_mulai', '<', $jamSelesai)
            ->where('jam_selesai', '>', $jamMulai)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId));

        if ((clone $base)->where('kelas_id', $kelasId)->exists()) {
            throw ValidationException::withMessages([
                'jam_mulai' => 'Jadwal bentrok: kelas ini sudah memiliki jadwal pada hari dan jam tersebut.',
            ]);
        }

        if ((clone $base)->where('guru_id', $guruId)->exists()) {
            throw ValidationException::withMessages([
                'guru_id' => 'Jadwal bentrok: guru ini sudah mengajar pada hari dan jam tersebut.',
            ]);
        }
    }

    private function ensureNoDuplicateKegiatan(int $semesterId, string $hari, int $mingguKe, ?int $ignoreId = null): void
    {
        $exists = JadwalKegiatan::where('semester_id', $semesterId)
            ->where('hari', $hari)
            ->where('minggu_ke', $mingguKe)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'minggu_ke' => 'Sudah ada kegiatan pada ' . $hari . ' minggu ke-' . $mingguKe . ' di semester ini.',
            ]);
        }
    }

    private function ensureKegiatanSelesaiSebelumMapel(int $semesterId, string $hari, string $jamSelesai): void
    {
        $mapelPertama = Jadwal::where('semester_id', $semesterId)
            ->where('hari', $hari)
            ->where('is_active', true)
            ->orderBy('jam_mulai')
            ->value('jam_mulai');

        if (!$mapelPertama) {
            return;
        }

        if ($jamSelesai > $mapelPertama) {
            throw ValidationException::withMessages([
                'jam_selesai' => 'Jam selesai kegiatan (' . $jamSelesai . ') harus sebelum jam mulai mapel pertama (' . substr((string) $mapelPertama, 0, 5) . ') di hari ' . $hari . '.',
            ]);
        }
    }
}