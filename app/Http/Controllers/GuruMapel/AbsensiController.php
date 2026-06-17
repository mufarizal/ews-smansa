<?php

namespace App\Http\Controllers\GuruMapel;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Jadwal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AbsensiController extends Controller
{
    public function index(Request $request)
    {
        $guru = Auth::user()->guru;

        if (!$guru) {
            abort(403, 'Data guru tidak ditemukan.');
        }

        $todayHari = $this->resolveHariToday();
        $now = now();

        $jadwals = Jadwal::query()
            ->with(['kelas.siswas', 'mapel'])
            ->where('guru_id', $guru->id)
            ->where('hari', $todayHari)
            ->orderBy('jam_mulai')
            ->get();

        $jadwals = $jadwals->map(function (Jadwal $jadwal) use ($now) {
            $jadwal->setAttribute('is_ongoing', $this->isJadwalOngoing($jadwal, $now));
            $jadwal->setAttribute('is_upcoming', $this->isJadwalUpcoming($jadwal, $now));
            $jadwal->setAttribute('is_finished', $this->isJadwalFinished($jadwal, $now));
            $jadwal->setAttribute('availability_message', $this->getAvailabilityMessage($jadwal, $now));

            return $jadwal;
        });

        $selectedJadwalId = $request->integer('jadwal_id')
            ?: $jadwals->firstWhere('is_ongoing', true)?->id
            ?: $jadwals->first()?->id;
        $selectedJadwal = $jadwals->firstWhere('id', $selectedJadwalId) ?? $jadwals->first();

        $activeJadwal = $jadwals->firstWhere('is_ongoing', true);

        return view('guru_mapel.absensi.index', [
            'guru' => $guru,
            'todayHari' => $todayHari,
            'jadwals' => $jadwals,
            'selectedJadwal' => $selectedJadwal,
            'activeJadwal' => $activeJadwal,
            'now' => $now,
        ]);
    }

    public function show(Jadwal $jadwal)
    {
        $guru = Auth::user()->guru;

        if (!$guru || (int) $jadwal->guru_id !== (int) $guru->id) {
            abort(403, 'Jadwal tidak tersedia untuk akun ini.');
        }

        $availabilityMessage = $this->getAvailabilityMessage($jadwal);

        if ($availabilityMessage !== null) {
            return redirect()->route('guru_mapel.absensi.index', ['jadwal_id' => $jadwal->id])
                ->with('error', $availabilityMessage);
        }

        $tanggal = now()->toDateString();
        $jadwal->load(['kelas.siswas.user', 'mapel', 'guru']);

        $attendances = Absensi::query()
            ->with(['siswa.kelas'])
            ->where('tipe', Absensi::TIPE_MAPEL)
            ->where('tanggal', $tanggal)
            ->where('jadwal_id', $jadwal->id)
            ->orderByDesc('updated_at')
            ->get();

        $attendanceByStudentId = $attendances->unique('siswa_id')->keyBy('siswa_id');
        $presentStudentIds = $attendanceByStudentId
            ->whereIn('status', ['hadir', 'terlambat'])
            ->pluck('siswa_id')
            ->values()
            ->all();

        return view('guru_mapel.absensi.show', [
            'jadwal' => $jadwal,
            'attendances' => $attendances,
            'attendanceByStudentId' => $attendanceByStudentId,
            'presentStudentIds' => $presentStudentIds,
            'students' => $jadwal->kelas?->siswas?->sortBy('nama')->values() ?? collect(),
            'tanggal' => $tanggal,
        ]);
    }

    public function store(Request $request, Jadwal $jadwal)
    {
        $guru = Auth::user()->guru;

        if (!$guru || (int) $jadwal->guru_id !== (int) $guru->id) {
            abort(403, 'Jadwal tidak tersedia untuk akun ini.');
        }

        $availabilityMessage = $this->getAvailabilityMessage($jadwal);

        if ($availabilityMessage !== null) {
            return redirect()->route('guru_mapel.absensi.index', ['jadwal_id' => $jadwal->id])
                ->with('error', $availabilityMessage);
        }

        $request->validate([
            'hadir_siswa_ids' => ['nullable', 'array'],
        ]);

        $tanggal = now()->toDateString();
        $studentIds = $jadwal->kelas?->siswas?->pluck('id')->map(fn($id) => (int) $id)->all() ?? [];

        $request->validate([
            'hadir_siswa_ids.*' => ['integer', Rule::in($studentIds)],
        ]);

        $presentStudentIds = collect($request->input('hadir_siswa_ids', []))
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values();

        $students = $jadwal->kelas?->siswas?->values() ?? collect();
        $now = now();

        DB::transaction(function () use ($students, $presentStudentIds, $jadwal, $tanggal, $now) {
            foreach ($students as $student) {
                $isPresent = $presentStudentIds->contains((int) $student->id);

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
                        'ip_address' => request()->ip(),
                        'jam_masuk' => $isPresent ? $now : null,
                        'jam_pulang' => null,
                        'status' => $isPresent ? 'hadir' : 'alpha',
                        'terlambat_menit' => 0,
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
            ->with('success', 'Checklist absensi berhasil disimpan.');
    }

    private function resolveHariToday(): string
    {
        return match (now()->dayOfWeekIso) {
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            default => 'Minggu',
        };
    }

    private function getAvailabilityMessage(Jadwal $jadwal, ?Carbon $now = null): ?string
    {
        $now ??= now();

        if ($jadwal->hari !== $this->resolveHariToday()) {
            return 'Jadwal ini tidak aktif hari ini.';
        }

        if (!$jadwal->isActiveToday()) {
            return 'Jadwal ini sedang nonaktif.';
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

        return $jadwal->hari === $this->resolveHariToday()
            && $jadwal->isActiveToday()
            && $now->lt($this->getScheduleStart($jadwal, $now));
    }

    private function isJadwalFinished(Jadwal $jadwal, ?Carbon $now = null): bool
    {
        $now ??= now();

        return $jadwal->hari === $this->resolveHariToday()
            && $jadwal->isActiveToday()
            && $now->gt($this->getScheduleEnd($jadwal, $now));
    }

    private function getScheduleStart(Jadwal $jadwal, Carbon $now): Carbon
    {
        return Carbon::parse(
            $now->toDateString() . ' ' . $jadwal->jam_mulai,
            config('app.timezone', 'Asia/Jakarta')
        );
    }

    private function getScheduleEnd(Jadwal $jadwal, Carbon $now): Carbon
    {
        return Carbon::parse(
            $now->toDateString() . ' ' . $jadwal->jam_selesai,
            config('app.timezone', 'Asia/Jakarta')
        );
    }
}