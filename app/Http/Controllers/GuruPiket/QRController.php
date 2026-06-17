<?php

namespace App\Http\Controllers\GuruPiket;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Kelas;
use App\Models\QRSession;
use App\Models\Siswa;
use App\Exports\AttendanceReportExport;
use App\Helpers\LocationHelper;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class QRController extends Controller
{
    /**
     * Menampilkan halaman generate QR absensi harian
     */
    public function index()
    {
        // Ambil QR sessions aktif dari database untuk hari ini - order by latest first
        $qrSessions = QRSession::where('tanggal', now()->format('Y-m-d'))
            ->where('sudah_ditutup', false)
            ->orderBy('updated_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $attendedStudents = [];
        if ($qrSessions->count() > 0) {
            // Ambil siswa yang sudah absen hari ini dengan detail lengkap
            $attendedStudents = Absensi::where('tanggal', now()->format('Y-m-d'))
                ->where('status', '!=', 'alpha')
                ->with(['siswa', 'siswa.kelas'])
                ->orderBy('jam_masuk', 'desc')
                ->get()
                ->map(function ($student) {
                    return [
                        'id' => $student->siswa_id,
                        'nama_siswa' => $student->siswa?->nama ?? 'N/A',
                        'jam_masuk' => $student->jam_masuk?->toDateTimeString(),
                        'jam_pulang' => $student->jam_pulang?->toDateTimeString(),
                        'status' => $student->status,
                        'distance' => isset($student->distance_meter)
                            ? LocationHelper::formatDistance($student->distance_meter)
                            : '-',
                        'terlambat_menit' => $student->terlambat_menit ?? 0,
                    ];
                })
                ->toArray();

            Log::info('Guru Piket viewing QR', [
                'guru_id' => Auth::user()->id,
                'tanggal' => now()->format('Y-m-d'),
                'num_sessions' => $qrSessions->count(),
                'jumlah_hadir' => count($attendedStudents),
            ]);
        }

        return view('guru_piket.absensi.qr', [
            'qrCodeUrl' => $qrSessions->isNotEmpty()
                ? route('siswa.qr.process', ['code' => $qrSessions->first()->kode_sesi])
                : route('siswa.qr.process'),
            'qrSessions' => $qrSessions,
            'attendedStudents' => $attendedStudents,
            'totalStudents' => $this->getTotalStudents(),
        ]);
    }

    /**
     * Generate QR code untuk absensi harian dengan tipe masuk/pulang
     * 
     * Masuk: Generates QR with jam_batas (threshold) dan jam_maksimal (hard deadline)
     * Pulang: Generates QR with jam_batas (earliest scan time)
     */
    public function generate(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date_format:Y-m-d|after_or_equal:today',
            'tipe' => 'required|in:masuk,pulang',
            'jam_batas' => 'required|date_format:H:i',
            'jam_maksimal' => 'required_if:tipe,masuk|nullable|date_format:H:i',
        ]);

        $tanggal = $request->tanggal;
        $tipe = $request->tipe;
        $jamBatas = $request->jam_batas;
        $jamMaksimal = $request->jam_maksimal;

        // Validasi: jam_maksimal harus lebih besar dari jam_batas untuk masuk
        if ($tipe === 'masuk' && $jamMaksimal) {
            $jamBatasTime = Carbon::createFromFormat('H:i', $jamBatas, 'Asia/Jakarta');
            $jamMaksimalTime = Carbon::createFromFormat('H:i', $jamMaksimal, 'Asia/Jakarta');

            if ($jamMaksimalTime->lessThanOrEqualTo($jamBatasTime)) {
                throw new \Exception('Jam maksimal harus lebih besar dari jam batas.');
            }
        }

        // CEK: Apakah sudah ada QR dengan tipe yang sama untuk tanggal ini?
        $existingQR = QRSession::where('tanggal', $tanggal)
            ->where('tipe', $tipe)
            ->where('sudah_ditutup', false)
            ->first();

        $currentQRSession = null;

        // Jika sudah ada QR aktif untuk hari ini dengan tipe yang sama, update session tersebut
        if ($existingQR) {
            $existingQR->update([
                'jam_batas' => Carbon::createFromFormat('Y-m-d H:i', $tanggal . ' ' . $jamBatas, 'Asia/Jakarta'),
                'jam_maksimal' => $tipe === 'masuk' && $jamMaksimal
                    ? Carbon::createFromFormat('Y-m-d H:i', $tanggal . ' ' . $jamMaksimal, 'Asia/Jakarta')
                    : null,
                'generated_at' => now(),
                'expire_at' => Carbon::parse($tanggal)->endOfDay(),
                'kode_sesi' => strtoupper(substr(md5($tanggal . $tipe . time()), 0, 8)),
            ]);

            $currentQRSession = $existingQR->fresh();

            Log::info('QR regenerated', [
                'tanggal' => $tanggal,
                'tipe' => $tipe,
                'jam_batas' => $currentQRSession->jam_batas?->toDateTimeString(),
                'jam_maksimal' => $currentQRSession->jam_maksimal?->toDateTimeString(),
                'kode_sesi' => $currentQRSession->kode_sesi,
                'dibuat_oleh' => Auth::user()->id,
            ]);
        } else {
            // Generate unique code untuk QR session
            $kodeSesi = strtoupper(substr(md5($tanggal . $tipe . time()), 0, 8));
            $qrGeneratedAt = now();
            $qrExpireTime = Carbon::parse($tanggal)->endOfDay(); // Berlaku sampai akhir hari

            // Convert time strings to full datetime (combine with tanggal)
            $jamBatasDateTime = Carbon::createFromFormat('Y-m-d H:i', $tanggal . ' ' . $jamBatas, 'Asia/Jakarta');
            $jamMaksimalDateTime = $tipe === 'masuk' && $jamMaksimal
                ? Carbon::createFromFormat('Y-m-d H:i', $tanggal . ' ' . $jamMaksimal, 'Asia/Jakarta')
                : null;

            // Create new QR session
            $newQR = QRSession::create([
                'tanggal' => $tanggal,
                'tipe' => $tipe,
                'jenis_sesi' => 'harian',
                'jam_batas' => $jamBatasDateTime,
                'jam_maksimal' => $jamMaksimalDateTime,
                'generated_at' => $qrGeneratedAt,
                'expire_at' => $qrExpireTime,
                'dibuat_oleh' => Auth::user()->id,
                'sudah_ditutup' => false,
                'kode_sesi' => $kodeSesi,
            ]);

            $currentQRSession = $newQR;

            Log::info('QR Generated New', [
                'tanggal' => $tanggal,
                'tipe' => $tipe,
                'jam_batas' => $jamBatasDateTime->toDateTimeString(),
                'jam_maksimal' => $jamMaksimalDateTime ? $jamMaksimalDateTime->toDateTimeString() : null,
                'kode_sesi' => $kodeSesi,
                'dibuat_oleh' => Auth::user()->id,
            ]);
        }

        // Ambil siswa yang sudah absen untuk tipe dan tanggal ini
        $attendedStudents = Absensi::where('tanggal', $tanggal)
            ->where('tipe', 'harian')
            ->with('siswa')
            ->orderBy('jam_masuk', 'desc')
            ->get()
            ->map(function ($absensi) {
                $jamMasuk = $absensi->jam_masuk ? $absensi->jam_masuk->format('H:i:s') : '-';
                $jamPulang = $absensi->jam_pulang ? $absensi->jam_pulang->format('H:i:s') : '-';
                $distance = $absensi->distance_meter ? LocationHelper::formatDistance($absensi->distance_meter) : '-';

                return [
                    'id' => $absensi->siswa_id,
                    'nama_siswa' => $absensi->siswa->nama ?? 'N/A',
                    'jam_masuk' => $jamMasuk,
                    'jam_pulang' => $jamPulang,
                    'status' => $absensi->status,
                    'distance' => $distance,
                    'terlambat_menit' => $absensi->terlambat_menit ?? 0,
                ];
            })
            ->toArray();

        // Re-fetch latest QR sessions to ensure latest data is displayed
        $qrSessions = QRSession::where('tanggal', $tanggal)
            ->where('sudah_ditutup', false)
            ->orderBy('updated_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
        ;

        return view('guru_piket.absensi.qr', [
            'qrCodeUrl' => $currentQRSession?->kode_sesi
                ? route('siswa.qr.process', ['code' => $currentQRSession->kode_sesi])
                : route('siswa.qr.process'),
            'qrSession' => $currentQRSession,
            'qrSessions' => $qrSessions,  // Re-fetch untuk ensure latest
            'attendedStudents' => $attendedStudents,
            'totalStudents' => $this->getTotalStudents(),
        ]);
    }

    /**
     * Get total students
     */
    private function getTotalStudents()
    {
        return \App\Models\Siswa::count();
    }

    /**
     * Refresh attendance via AJAX - with distance tracking
     */
    public function refreshAttendance()
    {
        $qrSession = QRSession::where('tanggal', now()->format('Y-m-d'))
            ->where('sudah_ditutup', false)
            ->orderBy('updated_at', 'desc')
            ->first();

        if (!$qrSession) {
            return response()->json(['error' => 'No active QR session'], 400);
        }

        $attendedStudents = Absensi::where('tanggal', $qrSession->tanggal)
            ->where('status', '!=', 'alpha')
            ->with(['siswa', 'siswa.kelas'])
            ->orderBy('jam_masuk', 'desc')
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->siswa_id,
                    'nama_siswa' => $student->siswa?->nama ?? 'N/A',
                    'jam_masuk' => $student->jam_masuk?->toDateTimeString(),
                    'jam_pulang' => $student->jam_pulang?->toDateTimeString(),
                    'status' => $student->status,
                    'distance' => isset($student->distance_meter)
                        ? LocationHelper::formatDistance($student->distance_meter)
                        : '-',
                    'terlambat_menit' => $student->terlambat_menit ?? 0,
                ];
            })
            ->toArray();

        return response()->json([
            'count' => count($attendedStudents),
            'students' => $attendedStudents,
            'total' => $this->getTotalStudents(),
        ]);
    }

    /**
     * Reset QR session
     */
    public function resetSession()
    {
        QRSession::where('sudah_ditutup', false)->update([
            'sudah_ditutup' => true,
            'expire_at' => now(),
        ]);

        return redirect()->route('guru_piket.qr')->with('success', 'Session QR telah direset');
    }

    /**
     * View history & report absensi
     */
    public function history(Request $request)
    {
        $sessionSearch = trim((string) $request->input('session_search', ''));
        $selectedSessionId = $request->integer('qr_session_id');
        $status = $request->input('status', 'semua');
        $kelasId = $request->input('kelas_id');
        $search = trim((string) $request->input('search', ''));

        $historySessions = QRSession::query()
            ->withCount('absensis')
            ->orderByDesc('generated_at')
            ->get();

        if ($sessionSearch !== '') {
            $needle = mb_strtolower($sessionSearch);

            $historySessions = $historySessions->filter(function (QRSession $session) use ($needle) {
                $haystack = mb_strtolower(implode(' ', array_filter([
                    Carbon::parse($session->tanggal)->format('d M Y'),
                    $session->generated_at?->format('H:i'),
                    $session->jam_batas?->format('H:i'),
                    $session->jam_maksimal?->format('H:i'),
                    $session->tipe === 'masuk' ? 'masuk' : 'pulang',
                    $session->kode_sesi,
                ])));

                return str_contains($haystack, $needle);
            })->values();
        }

        $availableHistorySessions = $historySessions
            ->groupBy(fn(QRSession $session) => Carbon::parse($session->tanggal)->format('Y-m-d'))
            ->map(function ($sessions, $tanggal) {
                return [
                    'tanggal' => $tanggal,
                    'date_label' => Carbon::parse($tanggal)->locale('id')->isoFormat('dddd, D MMMM YYYY'),
                    'sessions' => $sessions->map(function (QRSession $session) {
                        return [
                            'id' => $session->id,
                            'tanggal' => Carbon::parse($session->tanggal)->format('Y-m-d'),
                            'generated_at' => $session->generated_at?->format('H:i'),
                            'jam_batas' => $session->jam_batas?->format('H:i'),
                            'jam_maksimal' => $session->jam_maksimal?->format('H:i'),
                            'tipe' => $session->tipe,
                            'tipe_label' => $session->tipe === 'masuk' ? 'Masuk' : 'Pulang',
                            'absensis_count' => $session->absensis_count ?? 0,
                            'status_label' => $session->sudah_ditutup ? 'Ditutup' : 'Aktif',
                            'kode_sesi' => $session->kode_sesi,
                        ];
                    })->values(),
                ];
            })
            ->values();

        $selectedSession = $historySessions->firstWhere('id', $selectedSessionId)
            ?? $historySessions->first();

        $selectedSessionId = $selectedSession?->id;

        $availableClasses = Kelas::query()
            ->orderBy('nama_kelas')
            ->get(['id', 'nama_kelas']);

        // Get current authenticated guru
        $currentGuru = Auth::user()->guru;

        $attendancesQuery = Absensi::query()
            ->where('tipe', Absensi::TIPE_HARIAN)
            ->with(['siswa.kelas', 'qrSession', 'guru']);

        if ($selectedSessionId) {
            $attendancesQuery->where('qr_session_id', $selectedSessionId);
        }

        // Auto-filter: only show attendance from current guru
        if ($currentGuru) {
            $attendancesQuery->where('guru_id', $currentGuru->id);
        }

        if ($kelasId) {
            $attendancesQuery->whereHas('siswa', function ($query) use ($kelasId) {
                $query->where('kelas_id', $kelasId);
            });
        }

        if ($search !== '') {
            $attendancesQuery->whereHas('siswa', function ($query) use ($search) {
                $query->where('nama', 'like', '%' . $search . '%')
                    ->orWhere('nis', 'like', '%' . $search . '%');
            });
        }

        if ($status && $status !== 'semua') {
            $attendancesQuery->where('status', $status);
        }

        $attendances = $attendancesQuery
            ->orderBy('jam_masuk', 'desc')
            ->paginate(20)
            ->withQueryString();

        $selectedSessionAttendanceQuery = Absensi::query()
            ->where('tipe', Absensi::TIPE_HARIAN)
            ->where('qr_session_id', $selectedSessionId);

        $totalRecorded = (clone $selectedSessionAttendanceQuery)->count();

        $stats = [
            'total' => Siswa::count(),
            'hadir' => (clone $selectedSessionAttendanceQuery)->where('status', 'hadir')->count(),
            'terlambat' => (clone $selectedSessionAttendanceQuery)->where('status', 'terlambat')->count(),
            'alpha' => (clone $selectedSessionAttendanceQuery)->where('status', 'alpha')->count(),
            'tercatat' => $totalRecorded,
            'filtered' => $attendances->total(),
        ];
        $stats['belum_absen'] = max($stats['total'] - $stats['tercatat'], 0);

        return view('guru_piket.absensi.history', [
            'attendances' => $attendances,
            'selectedSession' => $selectedSession,
            'selectedSessionId' => $selectedSessionId,
            'status' => $status,
            'kelasId' => $kelasId,
            'search' => $search,
            'sessionSearch' => $sessionSearch,
            'stats' => $stats,
            'availableHistorySessions' => $availableHistorySessions,
            'availableClasses' => $availableClasses,
            'currentGuru' => $currentGuru,
        ]);
    }

    /**
     * Export attendance report to Excel
     */
    public function exportReport(Request $request)
    {
        $qrSessionId = $request->integer('qr_session_id');
        $status = $request->input('status', 'semua');
        $kelasId = $request->input('kelas_id');
        $search = trim((string) $request->input('search', ''));
        $format = $request->input('format', 'xlsx');

        try {
            $selectedSession = $qrSessionId ? QRSession::find($qrSessionId) : null;
            $fileName = 'Laporan-Absensi-' . ($selectedSession ? Carbon::parse($selectedSession->tanggal)->format('d-m-Y') : now()->format('d-m-Y'));
            if ($selectedSession) {
                $fileName .= '-' . $selectedSession->generated_at?->format('His');
            }

            if ($format === 'xlsx') {
                return Excel::download(
                    new AttendanceReportExport($qrSessionId, $status, $kelasId, $search),
                    $fileName . '.xlsx'
                );
            } else {
                // CSV format
                return Excel::download(
                    new AttendanceReportExport($qrSessionId, $status, $kelasId, $search),
                    $fileName . '.csv'
                );
            }
        } catch (\Exception $e) {
            return redirect()->route('guru_piket.attendance.history')
                ->with('error', 'Gagal export laporan: ' . $e->getMessage());
        }
    }
}

