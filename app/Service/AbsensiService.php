<?php

namespace App\Service;

use App\Helpers\LocationHelper;
use App\Models\Absensi;
use App\Models\QRSession;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AbsensiService
{
    /**
     * Generate device fingerprint dari browser untuk unique device identification
     * Menggunakan User-Agent, screen resolution, timezone, dll
     */
    public function generateDeviceId($request)
    {
        $ua = $request->userAgent();
        $screenInfo = $request->header('x-screen-info') ?? 'unknown'; // JS akan compute ini
        $userId = Auth::user()?->id ?? 'anonymous';

        // Generate hash dari multiple factors untuk unique device ID
        $fingerprint = hash('sha256', $ua.$screenInfo.$userId);

        return substr($fingerprint, 0, 16);
    }

    /**
     * Validate GPS accuracy - must be accurate enough (within school compound typically < 50m)
     */
    private function validateGPS($latitude, $longitude, $accuracy)
    {
        // GPS fields wajib ada
        if (! $latitude || ! $longitude) {
            throw new \Exception('GPS tidak tersedia. Pastikan lokasi sudah di-izinkan dan akurat.');
        }

        // Accuracy should be reasonable (< 30m typical untuk indoor/outdoor)
        if ($accuracy > 100) {
            throw new \Exception('GPS accuracy rendah ('.round($accuracy).'m). Pastikan di lokasi terbuka.');
        }

        return true;
    }

    /**
     * Handle absensi harian dengan tipe masuk/pulang:
     * tipe 'masuk': Check-in dengan validation jam_batas (threshold) dan jam_maksimal (hard deadline)
     * tipe 'pulang': Check-out dengan validation jam_batas (earliest scan time)
     *
     * Kedua tipe wajib dengan distance validation
     */
    public function handleHarian($siswa, $tipe, $latitude = null, $longitude = null, $accuracy = null, $deviceId = null, ?QRSession $qrSession = null)
    {
        $today = now()->toDateString();
        $now = now();

        // ========================================
        // VALIDATE GPS (Wajib)
        // ========================================
        $this->validateGPS($latitude, $longitude, $accuracy);

        // Validate device_id
        if (! $deviceId) {
            throw new \Exception('Device ID tidak valid.');
        }

        // Validate tipe parameter
        if (! in_array($tipe, ['masuk', 'pulang'])) {
            throw new \Exception('Tipe absensi tidak valid. Gunakan masuk atau pulang.');
        }

        // ========================================
        // GET QR SESSION dengan tipe matching
        // ========================================
        if (! $qrSession) {
            $qrSession = QRSession::where('tanggal', $today)
                ->where('sudah_ditutup', false)
                ->where('tipe', $tipe)
                ->where('jenis_sesi', QRSession::JENIS_HARIAN)
                ->first();
        }

        if (! $qrSession) {
            throw new \Exception("Belum ada QR session tipe '$tipe' untuk hari ini. Minta guru piket untuk generate QR.");
        }

        // ========================================
        // VALIDATE DISTANCE (Wajib dalam <= 500m dari sekolah)
        // ========================================
        $distanceMeters = LocationHelper::getDistanceFromSchool($latitude, $longitude);
        $schoolConfig = config('sekolah');
        $maxDistance = $schoolConfig['max_distance_meter'] ?? 500;

        if ($distanceMeters > $maxDistance) {
            throw new \Exception('Anda terlalu jauh dari sekolah ('.LocationHelper::formatDistance($distanceMeters).'). Minimal harus dalam '.LocationHelper::formatDistance($maxDistance).' dari sekolah.');
        }

        // ========================================
        // CHECK: Apakah siswa sudah absen dengan device ini hari ini?
        // ========================================
        $existingAbsensi = Absensi::where('siswa_id', $siswa->id)
            ->where('tanggal', $today)
            ->where('tipe', 'harian')
            ->where('device_id', $deviceId)
            ->first();

        // ========================================
        // TIPE MASUK: CHECK-IN LOGIC
        // ========================================
        if ($tipe === 'masuk') {
            // Jika sudah ada absensi hari ini dengan device ini
            if ($existingAbsensi) {
                // Jika jam_masuk sudah terisi = sudah check-in, error
                if ($existingAbsensi->jam_masuk) {
                    throw new \Exception('Anda sudah check-in hari ini dari device ini. Tidak bisa scan lagi.');
                }
            }

            // Validate jam_batas dan jam_maksimal
            $jamBatas = Carbon::parse($qrSession->jam_batas);
            $jamMaksimal = Carbon::parse($qrSession->jam_maksimal);

            // Check: Sudah lewat hard deadline (jam_maksimal)?
            if ($now->gt($jamMaksimal)) {
                throw new \Exception('Sudah melewati jam maksimal absensi masuk ('.$jamMaksimal->format('H:i:s').'). Tidak bisa absen masuk lagi.');
            }

            // Determine status: hadir atau terlambat
            $status = 'hadir';
            $terlambatMenit = 0;

            if ($now->gt($jamBatas)) {
                $status = 'terlambat';
                $terlambatMenit = (int) ceil($jamBatas->diffInSeconds($now) / 60);
            }

            // Create or update absensi record
            if ($existingAbsensi) {
                // Update existing record with jam_masuk
                $existingAbsensi->update([
                    'jam_masuk' => $now,
                    'status' => $status,
                    'terlambat_menit' => $terlambatMenit,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'akurasi_meter' => $accuracy,
                    'distance_meter' => $distanceMeters,
                    'qr_session_id' => $qrSession->id,
                ]);
                $absensi = $existingAbsensi;
            } else {
                // Create new record
                $absensi = Absensi::create([
                    'siswa_id' => $siswa->id,
                    'tanggal' => $today,
                    'tipe' => 'harian',
                    'jam_masuk' => $now,
                    'status' => $status,
                    'terlambat_menit' => $terlambatMenit,
                    'ip_address' => request()->ip(),
                    'qr_session_id' => $qrSession->id,
                    'device_id' => $deviceId,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'akurasi_meter' => $accuracy,
                    'distance_meter' => $distanceMeters,
                ]);
            }

            Log::info('Student checkin (masuk)', [
                'siswa_id' => $siswa->id,
                'tanggal' => $today,
                'tipe' => 'masuk',
                'device_id' => $deviceId,
                'jam_masuk' => $now->toDateTimeString(),
                'status' => $status,
                'terlambat_menit' => $terlambatMenit,
                'distance_meter' => $distanceMeters,
                'location' => "($latitude, $longitude), accuracy: {$accuracy}m",
            ]);

            return [
                'status' => 'checkin',
                'message' => 'Check-in berhasil! Waktu masuk: '.$now->format('H:i:s').' ('.$status.')',
                'jam_masuk' => $now->format('H:i:s'),
                'attendance_status' => $status,
                'terlambat_menit' => $terlambatMenit,
                'distance' => LocationHelper::formatDistance($distanceMeters),
            ];
        }

        // ========================================
        // TIPE PULANG: CHECK-OUT LOGIC
        // ========================================
        if ($tipe === 'pulang') {
            // Harus ada absensi masuk terlebih dahulu
            if (! $existingAbsensi) {
                throw new \Exception('Anda belum check-in hari ini. Harus check-in sebelum check-out.');
            }

            // Jika jam_masuk belum terisi = error (tidak valid state)
            if (! $existingAbsensi->jam_masuk) {
                throw new \Exception('Status absensi tidak valid. Hubungi guru piket.');
            }

            // Jika jam_pulang sudah terisi = sudah check-out
            if ($existingAbsensi->jam_pulang) {
                throw new \Exception('Anda sudah check-out hari ini dari device ini. Tidak bisa scan lagi.');
            }

            // Validate jam_batas (earliest scan time untuk pulang)
            $jamBatas = Carbon::parse($qrSession->jam_batas);

            // Check: Masih terlalu pagi untuk scan pulang?
            if ($now->lt($jamBatas)) {
                throw new \Exception('Belum waktunya checkout. Checkout bisa dimulai jam '.$jamBatas->format('H:i:s').'.');
            }

            // Update dengan jam_pulang
            $existingAbsensi->update([
                'jam_pulang' => $now,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'akurasi_meter' => $accuracy,
                'distance_meter' => $distanceMeters,
                'qr_session_id' => $qrSession->id,  // Update ke QR session terbaru (jika di-regenerate)
            ]);

            Log::info('Student checkout (pulang)', [
                'siswa_id' => $siswa->id,
                'tanggal' => $today,
                'tipe' => 'pulang',
                'device_id' => $deviceId,
                'jam_pulang' => $now->toDateTimeString(),
                'distance_meter' => $distanceMeters,
                'location' => "($latitude, $longitude), accuracy: {$accuracy}m",
            ]);

            return [
                'status' => 'checkout',
                'message' => 'Check-out berhasil! Waktu pulang: '.$now->format('H:i:s'),
                'jam_pulang' => $now->format('H:i:s'),
                'distance' => LocationHelper::formatDistance($distanceMeters),
            ];
        }
    }

    /**
     * Handle absensi mapel berdasarkan QR session jadwal tertentu.
     */
    public function handleMapel($siswa, QRSession $qrSession, $latitude = null, $longitude = null, $accuracy = null, $deviceId = null)
    {
        $today = now()->toDateString();
        $now = now();

        $this->validateGPS($latitude, $longitude, $accuracy);

        if (! $deviceId) {
            throw new \Exception('Device ID tidak valid.');
        }

        $qrSession->loadMissing(['jadwal.kelas', 'jadwal.mapel', 'jadwal.guru']);

        if ($qrSession->jenis_sesi !== QRSession::JENIS_MAPEL) {
            throw new \Exception('QR session bukan untuk absensi mapel.');
        }

        if (! $qrSession->jadwal_id || ! $qrSession->kelas_id || ! $qrSession->mapel_id) {
            throw new \Exception('QR mapel belum memiliki konteks jadwal yang lengkap.');
        }

        if ((int) $siswa->kelas_id !== (int) $qrSession->kelas_id) {
            throw new \Exception('QR mapel ini bukan untuk kelas Anda.');
        }

        if ($qrSession->expire_at && $now->gt(Carbon::parse($qrSession->expire_at))) {
            throw new \Exception('Session absensi mapel sudah berakhir.');
        }

        $distanceMeters = LocationHelper::getDistanceFromSchool($latitude, $longitude);
        $schoolConfig = config('sekolah');
        $maxDistance = $schoolConfig['max_distance_meter'] ?? 500;

        if ($distanceMeters > $maxDistance) {
            throw new \Exception('Anda terlalu jauh dari sekolah ('.LocationHelper::formatDistance($distanceMeters).'). Minimal harus dalam '.LocationHelper::formatDistance($maxDistance).' dari sekolah.');
        }

        $existingAbsensi = Absensi::where('siswa_id', $siswa->id)
            ->where('qr_session_id', $qrSession->id)
            ->first();

        if ($existingAbsensi) {
            throw new \Exception('Anda sudah absen pada sesi mapel ini.');
        }

        $absensi = Absensi::create([
            'siswa_id' => $siswa->id,
            'tanggal' => $today,
            'tipe' => Absensi::TIPE_MAPEL,
            'jam_masuk' => $now,
            'status' => 'hadir',
            'terlambat_menit' => 0,
            'jadwal_id' => $qrSession->jadwal_id,
            'guru_id' => $qrSession->jadwal?->guru_id,
            'mapel_id' => $qrSession->mapel_id,
            'ip_address' => request()->ip(),
            'qr_session_id' => $qrSession->id,
            'device_id' => $deviceId,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'akurasi_meter' => $accuracy,
            'distance_meter' => $distanceMeters,
        ]);

        $qrSession->increment('jumlah_hadir');

        Log::info('Student attendance mapel', [
            'siswa_id' => $siswa->id,
            'qr_session_id' => $qrSession->id,
            'jadwal_id' => $qrSession->jadwal_id,
            'kelas_id' => $qrSession->kelas_id,
            'mapel_id' => $qrSession->mapel_id,
            'jam_masuk' => $now->toDateTimeString(),
            'distance_meter' => $distanceMeters,
        ]);

        return [
            'status' => 'mapel',
            'message' => 'Absensi mapel berhasil! Waktu tercatat: '.$now->format('H:i:s'),
            'jam_masuk' => $now->format('H:i:s'),
            'distance' => LocationHelper::formatDistance($distanceMeters),
        ];
    }
}
