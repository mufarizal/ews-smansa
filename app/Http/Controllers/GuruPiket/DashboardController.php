<?php

namespace App\Http\Controllers\GuruPiket;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\QRSession;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $currentGuru = Auth::user()->guru;
        $todayHari = Jadwal::carbonToHari(now());
        $todayDate = now()->toDateString();

        // A. Status Bertugas Hari Ini
        $isOnDutyToday = false;
        if ($currentGuru) {
            $isOnDutyToday = $currentGuru->isPiketOnDay($todayHari);
        }

        // B. Jadwal Piket Saya (konversi dari bahasa Inggris ke Indonesia untuk display)
        $piketDaysRaw = [];
        if ($currentGuru) {
            $piketDaysRaw = $currentGuru->getPiketDaysActiveSemester();
        }
        $piketDays = array_map(fn($h) => Guru::convertHariToIndonesia($h), $piketDaysRaw);

        // C. Statistik Ringkas
        $sessionsToday = 0;
        $hadirToday = 0;
        $terlambatToday = 0;

        if ($isOnDutyToday) {
            $sessionsToday = QRSession::where('tanggal', $todayDate)
                ->where('sudah_ditutup', false)
                ->count();

            $hadirToday = Absensi::where('tanggal', $todayDate)
                ->where('status', 'hadir')
                ->where('guru_id', $currentGuru->id)
                ->count();

            $terlambatToday = Absensi::where('tanggal', $todayDate)
                ->where('status', 'terlambat')
                ->where('guru_id', $currentGuru->id)
                ->count();
        }

        return view('guru_piket.dashboard', [
            'isOnDutyToday' => $isOnDutyToday,
            'todayHari' => $todayHari,
            'piketDays' => $piketDays,
            'sessionsToday' => $sessionsToday,
            'hadirToday' => $hadirToday,
            'terlambatToday' => $terlambatToday,
        ]);
    }
}
