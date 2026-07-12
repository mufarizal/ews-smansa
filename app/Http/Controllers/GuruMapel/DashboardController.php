<?php

namespace App\Http\Controllers\GuruMapel;

use App\Http\Controllers\Controller;
use App\Models\GuruMapelKelas;
use App\Models\HasilUjian;
use App\Models\Jadwal;
use App\Models\NilaiTugas;
use App\Models\PerilakuSiswa;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $guru = Auth::user()->guru;

        if (! $guru) {
            return view('guru_mapel.dashboard', [
                'guru' => null,
                'totalKelas' => 0,
                'todayHari' => Jadwal::carbonToHari(now()),
                'todayDate' => now()->locale('id')->isoFormat('dddd, D MMMM YYYY'),
                'jadwalHariIni' => collect(),
                'positifCount' => 0,
                'negatifCount' => 0,
                'totalPerilaku' => 0,
                'recentActivities' => collect(),
            ]);
        }

        $guruMapelKelasIds = GuruMapelKelas::where('guru_id', $guru->id)
            ->pluck('id')
            ->toArray();

        $todayHari = Jadwal::carbonToHari(now());
        $todayDate = now()->locale('id')->isoFormat('dddd, D MMMM YYYY');

        $jadwalHariIni = Jadwal::with(['kelas', 'mapel'])
            ->where('guru_id', $guru->id)
            ->where('hari', $todayHari)
            ->whereHas('semester', fn ($q) => $q->where('is_active', true))
            ->orderBy('jam_mulai')
            ->get();

        $totalKelas = GuruMapelKelas::where('guru_id', $guru->id)
            ->select('kelas_id')
            ->distinct()
            ->count();

        $positifCount = PerilakuSiswa::where('guru_id', $guru->id)
            ->whereHas('perilaku', fn ($q) => $q->where('jenis', 'positif'))
            ->count();

        $negatifCount = PerilakuSiswa::where('guru_id', $guru->id)
            ->whereHas('perilaku', fn ($q) => $q->where('jenis', 'negatif'))
            ->count();

        $totalPerilaku = PerilakuSiswa::where('guru_id', $guru->id)->count();

        $recentNilaiTugas = NilaiTugas::whereHas('tugas', function ($q) use ($guruMapelKelasIds) {
            $q->whereIn('guru_mapel_kelas_id', $guruMapelKelasIds);
        })
            ->with(['siswa', 'tugas.guruMapelKelas.kelas'])
            ->latest()
            ->limit(8)
            ->get();

        $recentHasilUjian = HasilUjian::whereHas('ujianHarian', function ($q) use ($guruMapelKelasIds) {
            $q->whereIn('guru_mapel_kelas_id', $guruMapelKelasIds);
        })
            ->with(['siswa', 'ujianHarian.guruMapelKelas.kelas'])
            ->latest()
            ->limit(8)
            ->get();

        $recentActivities = collect();

        foreach ($recentNilaiTugas as $item) {
            $recentActivities->push([
                'type' => 'tugas',
                'date' => $item->created_at,
                'siswa' => $item->siswa,
                'kelas' => $item->tugas->guruMapelKelas->kelas ?? null,
                'judul' => $item->tugas->judul,
                'nilai' => $item->nilai,
                'status' => $item->status,
                'route' => route('guru_mapel.tugas.show', $item->tugas),
            ]);
        }

        foreach ($recentHasilUjian as $item) {
            $recentActivities->push([
                'type' => 'ujian',
                'date' => $item->created_at,
                'siswa' => $item->siswa,
                'kelas' => $item->ujianHarian->guruMapelKelas->kelas ?? null,
                'judul' => $item->ujianHarian->judul,
                'nilai' => $item->nilai,
                'status' => null,
                'route' => route('guru_mapel.ujian.hasil.index', $item->ujianHarian),
            ]);
        }

        $recentActivities = $recentActivities->sortByDesc('date')->take(12);

        return view('guru_mapel.dashboard', [
            'guru' => $guru,
            'totalKelas' => $totalKelas,
            'todayHari' => $todayHari,
            'todayDate' => $todayDate,
            'jadwalHariIni' => $jadwalHariIni,
            'positifCount' => $positifCount,
            'negatifCount' => $negatifCount,
            'totalPerilaku' => $totalPerilaku,
            'recentActivities' => $recentActivities,
        ]);
    }
}
