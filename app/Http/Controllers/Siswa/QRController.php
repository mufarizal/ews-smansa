<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\QRSession;
use App\Service\AbsensiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class QRController extends Controller
{
    protected $absensiService;

    public function __construct(AbsensiService $absensiService)
    {
        $this->absensiService = $absensiService;
    }

    public function scan()
    {
        $siswa = Auth::user()->siswa;

        if (! $siswa) {
            Log::warning('Siswa tidak memiliki data - User ID: '.Auth::user()->id);
        }

        return view('siswa.absensi.qr');
    }

    /**
     * Memproses absensi harian - Support JSON response untuk AJAX
     * Expect POST payload dengan GPS data dan device_id dari frontend
     */
    public function process(Request $request)
    {
        $user = Auth::user();
        $siswa = $user->siswa;
        $wantsJsonResponse = $this->wantsJsonResponse($request);
        $qrPayload = $request->input('qr_code');
        $resolvedSession = $this->resolveSessionFromPayload($qrPayload);

        Log::info('QR Process - User: '.$user->email.', Siswa: '.($siswa ? $siswa->id : 'null'));

        if (! $siswa) {
            $message = 'Data siswa tidak ditemukan. Hubungi admin.';
            if ($wantsJsonResponse) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 422);
            }

            return redirect()->back()->with('error', $message);
        }

        try {
            // Extract GPS dari request
            $latitude = $request->input('latitude');
            $longitude = $request->input('longitude');
            $accuracy = $request->input('accuracy');
            $deviceId = $request->input('device_id');

            // Validate inputs
            if (! $latitude || ! $longitude || ! $accuracy || ! $deviceId) {
                throw new \Exception('GPS atau Device ID tidak valid. Pastikan izin lokasi sudah aktif.');
            }

            // Convert to float untuk safety
            $latitude = (float) $latitude;
            $longitude = (float) $longitude;
            $accuracy = (float) $accuracy;

            if ($resolvedSession) {
                if ($resolvedSession->jenis_sesi === QRSession::JENIS_MAPEL) {
                    $result = $this->absensiService->handleMapel(
                        $siswa,
                        $resolvedSession,
                        $latitude,
                        $longitude,
                        $accuracy,
                        $deviceId
                    );
                } else {
                    $result = $this->absensiService->handleHarian(
                        $siswa,
                        $resolvedSession->tipe ?? 'masuk',
                        $latitude,
                        $longitude,
                        $accuracy,
                        $deviceId,
                        $resolvedSession
                    );
                }
            } else {
                // ========================================
                // DETERMINE TIPE: Check which QR types are available today
                // Logic: Try masuk first (if not done), otherwise try pulang
                // ========================================
                $today = now()->toDateString();

                $existingAbsensi = Absensi::where('siswa_id', $siswa->id)
                    ->where('tanggal', $today)
                    ->where('tipe', 'harian')
                    ->where('device_id', $deviceId)
                    ->first();

                $masuqQR = QRSession::where('tanggal', $today)
                    ->where('tipe', 'masuk')
                    ->where('jenis_sesi', QRSession::JENIS_HARIAN)
                    ->where('sudah_ditutup', false)
                    ->first();

                $pulangQR = QRSession::where('tanggal', $today)
                    ->where('tipe', 'pulang')
                    ->where('jenis_sesi', QRSession::JENIS_HARIAN)
                    ->where('sudah_ditutup', false)
                    ->first();

                $tipeToUse = null;

                if ($existingAbsensi && $existingAbsensi->jam_masuk) {
                    if ($pulangQR) {
                        $tipeToUse = 'pulang';
                    } else {
                        throw new \Exception('Anda sudah check-in. Belum ada QR untuk check-out. Minta guru piket untuk generate QR pulang.');
                    }
                } else {
                    if ($masuqQR) {
                        $tipeToUse = 'masuk';
                    } else {
                        throw new \Exception('Belum ada QR session untuk check-in. Minta guru piket untuk generate QR masuk.');
                    }
                }

                $result = $this->absensiService->handleHarian(
                    $siswa,
                    $tipeToUse,
                    $latitude,
                    $longitude,
                    $accuracy,
                    $deviceId,
                    $tipeToUse === 'masuk' ? $masuqQR : $pulangQR
                );
            }

            Log::info('Absensi success', [
                'siswa_id' => $siswa->id,
                'tipe' => $result['status'] ?? ($resolvedSession?->jenis_sesi ?? 'harian'),
                'result_status' => $result['status'],
                'distance' => $result['distance'] ?? 'N/A',
            ]);

            if ($wantsJsonResponse) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'status' => $result['status'],
                    'jam_masuk' => $result['jam_masuk'] ?? null,
                    'jam_pulang' => $result['jam_pulang'] ?? null,
                    'distance' => $result['distance'] ?? null,
                ], 200);
            }

            return redirect()->back()->with('success', $result['message']);

        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();

            Log::warning('Absensi Error: '.$errorMessage, [
                'siswa_id' => $siswa->id,
            ]);

            if ($wantsJsonResponse) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                ], 422);
            }

            return redirect()->back()->with('error', $errorMessage);
        }
    }

    private function wantsJsonResponse(Request $request): bool
    {
        return $request->expectsJson() || $request->isJson() || $request->ajax();
    }

    private function resolveSessionFromPayload(?string $payload): ?QRSession
    {
        if (! $payload) {
            return null;
        }

        $code = $payload;
        $query = parse_url($payload, PHP_URL_QUERY);

        if ($query) {
            parse_str($query, $params);
            if (! empty($params['code'])) {
                $code = $params['code'];
            }
        }

        return QRSession::where('kode_sesi', $code)->first();
    }
}
