<?php

namespace App\Http\Controllers\GuruMapel;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Bab;
use App\Models\GuruMapelKelas;
use App\Models\Jadwal;
use App\Models\Materi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AbsensiController extends Controller
{
    private const ATTENDANCE_STATUSES = ['hadir', 'izin', 'sakit', 'alpha', 'terlambat'];

    // =========================================================================
    // INDEX — daftar jadwal hari ini
    // =========================================================================

    public function index(Request $request)
    {
        $guru = Auth::user()->guru;

        if (! $guru) {
            abort(403, 'Data guru tidak ditemukan.');
        }

        $todayHari = $this->resolveHariToday();
        $now = now();

        $jadwals = Jadwal::query()
            ->with(['kelas.siswas', 'mapel', 'guru', 'semester'])
            ->where('guru_id', $guru->id)
            ->where('hari', $todayHari)
            ->orderBy('jam_mulai')
            ->get();

        $jadwalIds = $jadwals->pluck('id')->all();

        // FIX #4 — COUNT(DISTINCT siswa_id) agar duplikasi record tidak mempengaruhi flag
        $attendanceCounts = Absensi::query()
            ->where('tipe', Absensi::TIPE_MAPEL)
            ->where('tanggal', $now->toDateString())
            ->whereIn('jadwal_id', $jadwalIds)
            ->select('jadwal_id', DB::raw('COUNT(DISTINCT siswa_id) as total'))
            ->groupBy('jadwal_id')
            ->get()
            ->keyBy('jadwal_id')
            ->map(fn ($row) => (int) $row->total);

        $jadwals = $jadwals->map(function (Jadwal $jadwal) use ($now, $attendanceCounts) {
            $jadwal->setAttribute('is_ongoing', $this->isJadwalOngoing($jadwal, $now));
            $jadwal->setAttribute('is_upcoming', $this->isJadwalUpcoming($jadwal, $now));
            $jadwal->setAttribute('is_finished', $this->isJadwalFinished($jadwal, $now));
            $jadwal->setAttribute('availability_message', $this->getAvailabilityMessage($jadwal, $now));
            $jadwal->setAttribute('attendance_count', $attendanceCounts[$jadwal->id] ?? 0);
            $jadwal->setAttribute('has_attendance_today', ($attendanceCounts[$jadwal->id] ?? 0) > 0);

            return $jadwal;
        });

        $selectedJadwalId = $request->integer('jadwal_id')
            ?: $jadwals->firstWhere('is_ongoing', true)?->id
            ?: $jadwals->first()?->id;

        $selectedJadwal = $jadwals->firstWhere('id', $selectedJadwalId) ?? $jadwals->first();
        $activeJadwal = $jadwals->firstWhere('is_ongoing', true);

        $summaryStats = [
            'total' => $jadwals->count(),
            'sudah' => $jadwals->where('has_attendance_today', true)->count(),
            'aktif_kosong' => $jadwals->where('is_ongoing', true)->where('has_attendance_today', false)->count(),
            'terlewat' => $jadwals->where('is_finished', true)->where('has_attendance_today', false)->count(),
        ];

        return view('guru_mapel.absensi.index', [
            'guru' => $guru,
            'todayHari' => $todayHari,
            'jadwals' => $jadwals,
            'selectedJadwal' => $selectedJadwal,
            'activeJadwal' => $activeJadwal,
            'now' => $now,
            'summaryStats' => $summaryStats,
        ]);
    }

    // =========================================================================
    // SHOW — form absensi / rekap
    // =========================================================================

    public function show(Jadwal $jadwal)
    {
        $guru = Auth::user()->guru;

        $now = now();
        $tanggal = $now->toDateString();

        $isOngoing = $this->isJadwalOngoing($jadwal, $now);
        $isFinished = $this->isJadwalFinished($jadwal, $now);

        // FIX #3 — isReadOnly mencakup SEMUA kondisi bukan-aktif:
        //   • jadwal sudah selesai hari ini      → read-only
        //   • jadwal hari lain (bukan hari ini)  → read-only (bukan hanya finished)
        //   • jadwal belum berlaku kurikulum     → read-only
        $isReadOnly = ! $isOngoing;

        $availabilityMessage = $this->getAvailabilityMessage($jadwal, $now);

        // Jika tidak ongoing DAN tidak finished hari ini → redirect (bukan sekedar read-only)
        // Finished hari ini masih boleh dibuka sebagai rekap
        if (! $isOngoing && ! $isFinished) {
            return redirect()
                ->route('guru_mapel.absensi.index', ['jadwal_id' => $jadwal->id])
                ->with('error', $availabilityMessage ?? 'Jadwal ini tidak bisa diakses saat ini.');
        }

        $jadwal->load(['kelas.siswas.user', 'mapel', 'guru', 'semester']);

        $guruMapelKelas = GuruMapelKelas::query()
            ->where('guru_id', $jadwal->guru_id)
            ->where('mapel_id', $jadwal->mapel_id)
            ->where('kelas_id', $jadwal->kelas_id)
            ->first();

        $babs = collect();
        if ($guruMapelKelas) {
            $babs = Bab::with('materi')
                ->where('guru_mapel_kelas_id', $guruMapelKelas->id)
                ->orderBy('urutan')
                ->get();
        }

        $lastAttendance = Absensi::query()
            ->where('tipe', Absensi::TIPE_MAPEL)
            ->where('tanggal', $tanggal)
            ->where('jadwal_id', $jadwal->id)
            ->whereNotNull('materi_id')
            ->orderByDesc('updated_at')
            ->first();

        $lastBabId = $lastAttendance?->bab_id;
        $lastMateriId = $lastAttendance?->materi_id;

        // FIX #5 — ambil satu record per siswa secara eksplisit via subquery,
        // tidak bergantung pada urutan collection untuk unique()
        $attendances = Absensi::query()
            ->with(['siswa.kelas'])
            ->where('tipe', Absensi::TIPE_MAPEL)
            ->where('tanggal', $tanggal)
            ->where('jadwal_id', $jadwal->id)
            ->orderByDesc('updated_at')
            ->get();

        // Ambil record terbaru per siswa secara eksplisit
        $attendanceByStudentId = $attendances
            ->groupBy('siswa_id')
            ->map(fn ($group) => $group->sortByDesc('updated_at')->first())
            ->keyBy('siswa_id');

        $students = $jadwal->kelas?->siswas?->sortBy('nama')->values() ?? collect();

        return view('guru_mapel.absensi.show', [
            'jadwal' => $jadwal,
            'attendances' => $attendances,
            'attendanceByStudentId' => $attendanceByStudentId,
            'students' => $students,
            'tanggal' => $tanggal,
            'isReadOnly' => $isReadOnly,
            'isFinished' => $isFinished,
            'isMissingAttendance' => ! $isReadOnly && $attendances->isEmpty(),
            'statusCounts' => $this->buildStatusCounts($attendanceByStudentId),
            'statusOptions' => self::ATTENDANCE_STATUSES,
            'babs' => $babs,
            'lastBabId' => $lastBabId,
            'lastMateriId' => $lastMateriId,
            'totalStudents' => $students->count(),
            'filledCount' => $attendanceByStudentId->count(),
        ]);
    }

    // =========================================================================
    // STORE — simpan / update absensi
    // =========================================================================

    public function store(Request $request, Jadwal $jadwal)
    {
        $guru = Auth::user()->guru;

        $now = now();
        $availabilityMessage = $this->getAvailabilityMessage($jadwal, $now);

        if ($availabilityMessage !== null) {
            return redirect()
                ->route('guru_mapel.absensi.index', ['jadwal_id' => $jadwal->id])
                ->with('error', $availabilityMessage);
        }

        $tanggal = $now->toDateString();
        $studentIds = $jadwal->kelas?->siswas?->pluck('id')->map(fn ($id) => (int) $id)->all() ?? [];

        $validated = $request->validate([
            'bab_id' => ['nullable', 'exists:babs,id'],
            'materi_id' => ['required', 'exists:materis,id'],
            'absensi' => ['required', 'array'],
            'absensi.*.status' => ['required', Rule::in(self::ATTENDANCE_STATUSES)],
            'absensi.*.terlambat_menit' => ['nullable', 'integer', 'min:0', 'max:600'],
        ]);

        $materi = Materi::findOrFail($validated['materi_id']);
        $babId = $validated['bab_id'] ?? $materi->bab_id;

        // Validasi konsistensi materi ↔ bab
        if ($validated['bab_id'] && (int) $materi->bab_id !== (int) $validated['bab_id']) {
            return back()
                ->withInput()
                ->withErrors(['materi_id' => 'Materi yang dipilih tidak sesuai dengan bab yang dipilih.']);
        }

        $input = collect($request->input('absensi', []));
        $students = $jadwal->kelas?->siswas?->values() ?? collect();
        $scheduleStart = $this->getScheduleStart($jadwal, $now);

        DB::transaction(function () use ($students, $input, $studentIds, $jadwal, $tanggal, $now, $scheduleStart, $babId, $materi) {
            foreach ($students as $student) {
                if (! in_array((int) $student->id, $studentIds, true)) {
                    continue;
                }

                $row = collect($input->get((string) $student->id, []));

                // FIX #1 — jika siswa tidak ada di input POST, SKIP (jangan timpa dengan default hadir)
                if ($row->isEmpty()) {
                    continue;
                }

                // FIX #1 — ambil existing record lengkap, bukan hanya status
                $existingRecord = Absensi::where('siswa_id', $student->id)
                    ->where('tanggal', $tanggal)
                    ->where('tipe', Absensi::TIPE_MAPEL)
                    ->where('jadwal_id', $jadwal->id)
                    ->first();

                $status = $row->get('status', $existingRecord?->status ?? 'hadir');
                $lateMinutes = (int) ($row->get('terlambat_menit') ?? 0);

                if (! in_array($status, self::ATTENDANCE_STATUSES, true)) {
                    $status = 'alpha';
                }

                if ($status === 'terlambat' && $lateMinutes === 0) {
                    $lateMinutes = max(1, (int) $scheduleStart->diffInMinutes($now, false));
                }

                if ($status !== 'terlambat') {
                    $lateMinutes = 0;
                }

                $hasArrivalTime = in_array($status, ['hadir', 'terlambat'], true);

                // FIX #4 — jam_masuk: pertahankan yang lama saat update,
                // hanya set saat CREATE baru atau saat status berubah dari non-hadir ke hadir/terlambat
                $wasPresent = $existingRecord && in_array($existingRecord->status, ['hadir', 'terlambat'], true);
                $nowPresent = $hasArrivalTime;
                $jamMasuk = match (true) {
                    // Ada record lama dan status hadir tetap hadir → pakai jam lama
                    $existingRecord !== null && $wasPresent && $nowPresent => $existingRecord->jam_masuk,
                    // Status baru adalah hadir/terlambat → set sekarang
                    $nowPresent => $now,
                    // Non-hadir
                    default => null,
                };

                Absensi::updateOrCreate(
                    [
                        'siswa_id' => $student->id,
                        'tanggal' => $tanggal,
                        'tipe' => Absensi::TIPE_MAPEL,
                        'jadwal_id' => $jadwal->id,
                    ],
                    [
                        'guru_id' => $jadwal->guru_id,
                        'mapel_id' => $jadwal->mapel_id,
                        'bab_id' => $babId,
                        'materi_id' => $materi->id,
                        'ip_address' => request()->ip(),
                        'jam_masuk' => $jamMasuk,
                        'jam_pulang' => null,
                        'status' => $status,
                        'terlambat_menit' => $lateMinutes,
                        'qr_session_id' => null,
                        'device_id' => null,
                        'latitude' => null,
                        'longitude' => null,
                        'akurasi_meter' => null,
                        'distance_meter' => null,
                    ]
                );
            }
        });

        return redirect()
            ->route('guru_mapel.absensi.show', $jadwal)
            ->with('success', 'Absensi mapel berhasil disimpan. Anda masih bisa mengubah selama jadwal berlangsung.');
    }

    // =========================================================================
    // API — materi by bab (untuk select dinamis)
    // =========================================================================

    public function getMaterisByBab(Request $request, Jadwal $jadwal)
    {
        $guru = Auth::user()->guru;

        $request->validate([
            'bab_id' => ['required', 'exists:babs,id'],
        ]);

        $materis = Materi::where('bab_id', $request->bab_id)
            ->orderBy('urutan')
            ->get(['id', 'judul', 'urutan']);

        return response()->json($materis);
    }

    // =========================================================================
    // Private helpers
    // =========================================================================

    private function resolveHariToday(): string
    {
        return Jadwal::carbonToHari(now());
    }

    private function getAvailabilityMessage(Jadwal $jadwal, ?Carbon $now = null): ?string
    {
        $now ??= now();

        if ($jadwal->hari !== Jadwal::carbonToHari($now)) {
            return 'Jadwal ini tidak aktif hari ini.';
        }

        if (! $jadwal->berlakuPadaTanggal($now->copy()->startOfDay())) {
            return 'Jadwal ini belum berlaku sesuai pengaturan kurikulum.';
        }

        if ($now->lt($this->getScheduleStart($jadwal, $now))) {
            return 'Belum masuk jadwal mengabsen.';
        }

        if ($now->gt($this->getScheduleEnd($jadwal, $now))) {
            return 'Waktu mengabsen untuk jadwal ini sudah berakhir.';
        }

        return null;
    }

    private function isJadwalOngoing(Jadwal $jadwal, ?Carbon $now = null): bool
    {
        return $this->getAvailabilityMessage($jadwal, $now) === null;
    }

    private function isJadwalUpcoming(Jadwal $jadwal, ?Carbon $now = null): bool
    {
        $now ??= now();

        if ($jadwal->hari !== Jadwal::carbonToHari($now)) {
            return false;
        }

        return $jadwal->berlakuPadaTanggal($now->copy()->startOfDay())
            && $now->lt($this->getScheduleStart($jadwal, $now));
    }

    private function isJadwalFinished(Jadwal $jadwal, ?Carbon $now = null): bool
    {
        $now ??= now();

        // FIX #3 — hanya jadwal HARI INI yang bisa dianggap finished
        if ($jadwal->hari !== Jadwal::carbonToHari($now)) {
            return false;
        }

        return $jadwal->berlakuPadaTanggal($now->copy()->startOfDay())
            && $now->gt($this->getScheduleEnd($jadwal, $now));
    }

    private function getScheduleStart(Jadwal $jadwal, Carbon $now): Carbon
    {
        return Carbon::parse(
            $now->toDateString().' '.$jadwal->jam_mulai,
            config('app.timezone', 'Asia/Jakarta')
        );
    }

    private function getScheduleEnd(Jadwal $jadwal, Carbon $now): Carbon
    {
        return Carbon::parse(
            $now->toDateString().' '.$jadwal->jam_selesai,
            config('app.timezone', 'Asia/Jakarta')
        );
    }

    private function buildStatusCounts($attendanceByStudentId): array
    {
        $counts = array_fill_keys(self::ATTENDANCE_STATUSES, 0);

        foreach ($attendanceByStudentId as $attendance) {
            if (array_key_exists($attendance->status, $counts)) {
                $counts[$attendance->status]++;
            }
        }

        return $counts;
    }
}
