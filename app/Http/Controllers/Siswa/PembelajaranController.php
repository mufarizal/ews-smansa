<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateAiRecommendationJob;
use App\Models\Absensi;
use App\Models\AiRecommendation;
use App\Models\EarlyWarningResult;
use App\Models\GuruMapelKelas;
use App\Models\HasilUjian;
use App\Models\Jadwal;
use App\Models\JawabanTugas;
use App\Models\JawabanUjian;
use App\Models\NilaiTugas;
use App\Models\PerilakuSiswa;
use App\Models\Siswa;
use App\Models\Tugas;
use App\Models\UjianHarian;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PembelajaranController extends Controller
{
    /**
     * Ambil model Siswa dari user yang sedang login.
     */
    private function getSiswa()
    {
        $siswa = Auth::user()?->siswa;

        abort_unless($siswa, 403, 'Akun siswa tidak ditemukan.');

        return $siswa;
    }

    private function getGuruMapelKelasIdsForSiswa(Siswa $siswa)
    {
        return GuruMapelKelas::where('kelas_id', $siswa->kelas_id)
            ->pluck('id')
            ->toArray();
    }

    private function buildAkademikSummary(Siswa $siswa, ?int $bulan = null, ?int $tahun = null): array
    {
        $guruMapelKelasIds = $this->getGuruMapelKelasIdsForSiswa($siswa);

        $tanggalQuery = function ($query, $bulan, $tahun) {
            if ($bulan && $tahun) {
                $query->whereMonth('tanggal_tugas', $bulan)
                    ->whereYear('tanggal_tugas', $tahun);
            }
        };

        $totalTugas = Tugas::whereIn('guru_mapel_kelas_id', $guruMapelKelasIds);
        if ($bulan && $tahun) {
            $totalTugas->whereMonth('tanggal_tugas', $bulan)->whereYear('tanggal_tugas', $tahun);
        }
        $totalTugas = $totalTugas->count();

        $tugasIds = Tugas::whereIn('guru_mapel_kelas_id', $guruMapelKelasIds);
        if ($bulan && $tahun) {
            $tugasIds->whereMonth('tanggal_tugas', $bulan)->whereYear('tanggal_tugas', $tahun);
        }
        $tugasIds = $tugasIds->pluck('id');

        $tugasSelesai = NilaiTugas::where('siswa_id', $siswa->id)
            ->whereIn('tugas_id', $tugasIds)
            ->where('status', 'selesai')
            ->count();

        $totalUjianPublish = UjianHarian::whereIn('guru_mapel_kelas_id', $guruMapelKelasIds)
            ->where('status', 'publish');
        if ($bulan && $tahun) {
            $totalUjianPublish->whereMonth('created_at', $bulan)->whereYear('created_at', $tahun);
        }
        $totalUjianPublish = $totalUjianPublish->count();

        $ujianDikerjakan = HasilUjian::where('siswa_id', $siswa->id)
            ->whereIn('ujian_harian_id', function ($query) use ($guruMapelKelasIds) {
                $query->select('id')
                    ->from('ujian_harians')
                    ->whereIn('guru_mapel_kelas_id', $guruMapelKelasIds);
            });
        if ($bulan && $tahun) {
            $ujianDikerjakan->whereMonth('created_at', $bulan)->whereYear('created_at', $tahun);
        }
        $ujianDikerjakan = $ujianDikerjakan->count();

        $harianHadir = Absensi::where('siswa_id', $siswa->id)->where('tipe', 'harian')->where('status', 'hadir');
        $totalAbsensiHarian = Absensi::where('siswa_id', $siswa->id)->where('tipe', 'harian');
        if ($bulan && $tahun) {
            $harianHadir->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun);
            $totalAbsensiHarian->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun);
        }
        $harianHadir = $harianHadir->count();
        $totalAbsensiHarian = $totalAbsensiHarian->count();

        $mapelHadir = Absensi::where('siswa_id', $siswa->id)->where('tipe', 'mapel')->where('status', 'hadir');
        $totalAbsensiMapel = Absensi::where('siswa_id', $siswa->id)->where('tipe', 'mapel');
        if ($bulan && $tahun) {
            $mapelHadir->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun);
            $totalAbsensiMapel->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun);
        }
        $mapelHadir = $mapelHadir->count();
        $totalAbsensiMapel = $totalAbsensiMapel->count();

        return [
            'totalTugas' => $totalTugas,
            'tugasSelesai' => $tugasSelesai,
            'totalUjianPublish' => $totalUjianPublish,
            'ujianDikerjakan' => $ujianDikerjakan,
            'harianHadir' => $harianHadir,
            'totalAbsensiHarian' => $totalAbsensiHarian,
            'mapelHadir' => $mapelHadir,
            'totalAbsensiMapel' => $totalAbsensiMapel,
        ];
    }

    public function dashboard()
    {
        $siswa = $this->getSiswa();

        $hariIni = Jadwal::with(['mapel', 'guru'])
            ->where('kelas_id', $siswa->kelas_id)
            ->where('hari', Jadwal::carbonToHari(Carbon::today()))
            ->orderBy('jam_mulai')
            ->get();

        $bulan = Carbon::now()->month;
        $tahun = Carbon::now()->year;
        $summary = $this->buildAkademikSummary($siswa, $bulan, $tahun);

        return view('siswa.dashboard', compact('siswa', 'summary', 'hariIni'));
    }

    public function profil(Request $request)
    {
        $siswa = $this->getSiswa();

        $tab = in_array($request->get('tab'), ['nilai', 'absensi'], true) ? $request->get('tab') : 'nilai';
        $bulan = (int) ($request->get('bulan', Carbon::now()->month));
        $tahun = (int) ($request->get('tahun', Carbon::now()->year));

        $summary = $this->buildAkademikSummary($siswa, $bulan, $tahun);

        $guruMapelKelasIds = $this->getGuruMapelKelasIdsForSiswa($siswa);

        $tugasRiwayat = Tugas::with([
            'materi.bab',
            'guruMapelKelas.mapel',
            'nilaiTugas' => function ($q) use ($siswa) {
                $q->where('siswa_id', $siswa->id);
            },
        ])
            ->whereIn('guru_mapel_kelas_id', $guruMapelKelasIds)
            ->whereMonth('tanggal_tugas', $bulan)
            ->whereYear('tanggal_tugas', $tahun)
            ->latest('tanggal_tugas')
            ->limit(10)
            ->get();

        $ujianRiwayat = HasilUjian::with([
            'ujianHarian.bab',
            'ujianHarian.guruMapelKelas.mapel',
        ])
            ->where('siswa_id', $siswa->id)
            ->whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun)
            ->latest()
            ->limit(10)
            ->get();

        $absensiHarian = Absensi::where('siswa_id', $siswa->id)
            ->where('tipe', 'harian')
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->orderByDesc('tanggal')
            ->limit(10)
            ->get();

        $absensiMapel = Absensi::where('siswa_id', $siswa->id)
            ->where('tipe', 'mapel')
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->orderByDesc('tanggal')
            ->limit(10)
            ->get();

        // --- Data perkembangan (semester aktif) ---
        $hasilEws = $siswa->hasil_saw_terbaru;

        $rekomendasi = AiRecommendation::untukSiswaSekarang($siswa->id);

        $aiRefreshing = false;

        if ($rekomendasi && $hasilEws && $hasilEws->generated_at) {
            $aiRefreshing = $rekomendasi->generated_at->lt($hasilEws->generated_at);
        }

        if ($aiRefreshing || ! $rekomendasi) {
            GenerateAiRecommendationJob::dispatch($siswa->id)->delay(now()->addSeconds(30));
        }

        $riwayatEws = EarlyWarningResult::where('siswa_id', $siswa->id)
            ->semesterAktif()
            ->orderBy('tanggal_hitung')
            ->get();

        $rataTugas = $siswa->nilai_tugas_avg;
        $rataUjian = $siswa->nilai_ujian_avg;
        $kehadiran = $siswa->kehadiran_persen;
        $skorPerilaku = $siswa->total_skor_perilaku;

        $perilakuPositif = PerilakuSiswa::where('siswa_id', $siswa->id)
            ->whereHas('perilaku', fn ($q) => $q->where('jenis', 'positif'))
            ->count();
        $perilakuNegatif = PerilakuSiswa::where('siswa_id', $siswa->id)
            ->whereHas('perilaku', fn ($q) => $q->where('jenis', 'negatif'))
            ->count();

        $riwayatPerilaku = PerilakuSiswa::with('perilaku')
            ->where('siswa_id', $siswa->id)
            ->orderByDesc('tanggal')
            ->limit(10)
            ->get();

        return view('siswa.profil.index', compact(
            'siswa',
            'summary',
            'tab',
            'tugasRiwayat',
            'ujianRiwayat',
            'absensiHarian',
            'absensiMapel',
            'bulan',
            'tahun',
            'hasilEws',
            'rekomendasi',
            'riwayatEws',
            'rataTugas',
            'rataUjian',
            'kehadiran',
            'skorPerilaku',
            'perilakuPositif',
            'perilakuNegatif',
            'riwayatPerilaku',
            'aiRefreshing'
        ));
    }

    // -----------------------------------------------------------------------
    // TUGAS
    // -----------------------------------------------------------------------

    /**
     * Daftar semua tugas untuk kelas siswa yang login.
     */
    public function tugasIndex()
    {
        $siswa = $this->getSiswa();

        $guruMapelKelasIds = GuruMapelKelas::where('kelas_id', $siswa->kelas_id)
            ->pluck('id');

        $tugas = Tugas::with(['materi.bab', 'guruMapelKelas.mapel', 'guruMapelKelas.guru', 'nilaiTugas' => function ($q) use ($siswa) {
            $q->where('siswa_id', $siswa->id);
        }])
            ->whereIn('guru_mapel_kelas_id', $guruMapelKelasIds)
            ->latest()
            ->get();

        return view('siswa.tugas.index', compact('tugas'));
    }

    /**
     * Detail satu tugas beserta nilai siswa ini (jika sudah dinilai).
     */
    public function tugasShow(Tugas $tugas)
    {
        $siswa = $this->getSiswa();

        $guruMapelKelasIds = GuruMapelKelas::where('kelas_id', $siswa->kelas_id)
            ->pluck('id')
            ->toArray();

        abort_unless(in_array($tugas->guru_mapel_kelas_id, $guruMapelKelasIds), 403);

        $tugas->load(['materi.bab', 'guruMapelKelas.mapel']);

        $nilai = NilaiTugas::where('tugas_id', $tugas->id)
            ->where('siswa_id', $siswa->id)
            ->first();

        return view('siswa.tugas.show', compact('tugas', 'nilai'));
    }

    // -----------------------------------------------------------------------
    // UJIAN HARIAN
    // -----------------------------------------------------------------------

    /**
     * Daftar ujian yang berstatus 'publish' untuk kelas siswa ini.
     */
    public function ujianIndex()
    {
        $siswa = $this->getSiswa();

        $guruMapelKelasIds = GuruMapelKelas::where('kelas_id', $siswa->kelas_id)
            ->pluck('id');

        $ujians = UjianHarian::with(['bab', 'guruMapelKelas.mapel', 'guruMapelKelas.guru'])
            ->whereIn('guru_mapel_kelas_id', $guruMapelKelasIds)
            ->where('status', 'publish')
            ->latest()
            ->get();

        return view('siswa.ujian.index', compact('ujians'));
    }

    /**
     * Halaman mulai ujian: tampilkan soal-soal tanpa kunci jawaban.
     * Jika siswa sudah mengerjakan, redirect ke halaman hasil.
     */
    public function ujianShow(UjianHarian $ujianHarian)
    {
        $siswa = $this->getSiswa();

        $guruMapelKelasIds = GuruMapelKelas::where('kelas_id', $siswa->kelas_id)
            ->pluck('id')
            ->toArray();

        abort_unless(in_array($ujianHarian->guru_mapel_kelas_id, $guruMapelKelasIds), 403);
        abort_unless($ujianHarian->status === 'publish', 403, 'Ujian belum tersedia.');

        // Jika sudah mengerjakan, langsung redirect ke hasil
        $sudahMengerjakan = HasilUjian::where('ujian_harian_id', $ujianHarian->id)
            ->where('siswa_id', $siswa->id)
            ->exists();

        if ($sudahMengerjakan) {
            return redirect()->route('siswa.ujian.hasil', $ujianHarian)
                ->with('info', 'Anda sudah mengerjakan ujian ini. Berikut hasil Anda.');
        }

        // Tampilkan soal tanpa kolom jawaban_benar
        $soals = $ujianHarian->soalUjians()
            ->select(['id', 'soal', 'opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'bobot'])
            ->get();

        $ujianHarian->load(['bab', 'guruMapelKelas.mapel']);

        return view('siswa.ujian.show', compact('ujianHarian', 'soals'));
    }

    /**
     * Siswa submit jawaban ujian.
     *
     * Flow:
     * 1. Validasi siswa belum pernah submit ujian ini.
     * 2. Simpan tiap jawaban ke jawaban_ujians dengan flag is_benar.
     * 3. Hitung total benar/salah dan nilai akhir berdasarkan bobot.
     * 4. Simpan rekap ke hasil_ujians.
     * 5. Redirect ke halaman hasil.
     */
    public function ujianSubmit(Request $request, UjianHarian $ujianHarian)
    {
        $siswa = $this->getSiswa();

        $guruMapelKelasIds = GuruMapelKelas::where('kelas_id', $siswa->kelas_id)
            ->pluck('id')
            ->toArray();

        abort_unless(in_array($ujianHarian->guru_mapel_kelas_id, $guruMapelKelasIds), 403);
        abort_unless($ujianHarian->status === 'publish', 403, 'Ujian belum tersedia.');

        // Cegah submit ganda
        $sudahMengerjakan = HasilUjian::where('ujian_harian_id', $ujianHarian->id)
            ->where('siswa_id', $siswa->id)
            ->exists();

        if ($sudahMengerjakan) {
            return redirect()->route('siswa.ujian.hasil', $ujianHarian)
                ->with('info', 'Anda sudah mengerjakan ujian ini.');
        }

        $request->validate([
            'jawaban' => ['required', 'array'],
            'jawaban.*.soal_id' => ['required', 'exists:soal_ujians,id'],
            'jawaban.*.jawaban' => ['required', 'in:a,b,c,d'],
        ]);

        // Load semua soal ujian ini beserta kunci jawaban dan bobot
        $soals = $ujianHarian->soalUjians()->get()->keyBy('id');

        $jumlahBenar = 0;
        $jumlahSalah = 0;
        $bobotBenar = 0;
        $totalBobot = $soals->sum('bobot');

        foreach ($request->jawaban as $item) {
            $soalId = $item['soal_id'];
            $jawaban = $item['jawaban'];

            // Abaikan jika soal_id tidak termasuk dalam ujian ini
            if (! $soals->has($soalId)) {
                continue;
            }

            $soal = $soals[$soalId];
            $isBenar = $soal->jawaban_benar === $jawaban;

            if ($isBenar) {
                $jumlahBenar++;
                $bobotBenar += $soal->bobot;
            } else {
                $jumlahSalah++;
            }

            JawabanUjian::create([
                'ujian_harian_id' => $ujianHarian->id,
                'soal_ujian_id' => $soalId,
                'siswa_id' => $siswa->id,
                'jawaban' => $jawaban,
                'is_benar' => $isBenar,
            ]);
        }

        // Nilai = (total bobot soal yang benar / total bobot seluruh soal) * 100
        $nilai = $totalBobot > 0 ? round(($bobotBenar / $totalBobot) * 100, 2) : 0;

        HasilUjian::create([
            'ujian_harian_id' => $ujianHarian->id,
            'siswa_id' => $siswa->id,
            'jumlah_benar' => $jumlahBenar,
            'jumlah_salah' => $jumlahSalah,
            'nilai' => $nilai,
        ]);

        return redirect()->route('siswa.ujian.hasil', $ujianHarian)
            ->with('success', 'Ujian berhasil dikumpulkan.');
    }

    /**
     * Siswa melihat hasil ujian dan review jawaban mereka.
     * Tampilkan jawaban siswa beserta kunci jawaban yang benar.
     */
    public function ujianHasil(UjianHarian $ujianHarian)
    {
        $siswa = $this->getSiswa();

        $guruMapelKelasIds = GuruMapelKelas::where('kelas_id', $siswa->kelas_id)
            ->pluck('id')
            ->toArray();

        abort_unless(in_array($ujianHarian->guru_mapel_kelas_id, $guruMapelKelasIds), 403);

        $hasil = HasilUjian::where('ujian_harian_id', $ujianHarian->id)
            ->where('siswa_id', $siswa->id)
            ->firstOrFail();

        // Ambil jawaban siswa beserta soal dan kunci jawaban untuk review
        $jawabanSiswa = JawabanUjian::with('soalUjian')
            ->where('ujian_harian_id', $ujianHarian->id)
            ->where('siswa_id', $siswa->id)
            ->get();

        $ujianHarian->load(['bab', 'guruMapelKelas.mapel']);

        return view('siswa.ujian.hasil', compact('ujianHarian', 'hasil', 'jawabanSiswa'));
    }

    /**
     * Halaman kerja tugas online: tampilkan soal-soal tanpa kunci jawaban.
     */
    public function tugasKerjakan(Tugas $tugas)
    {
        $siswa = $this->getSiswa();

        $guruMapelKelasIds = GuruMapelKelas::where('kelas_id', $siswa->kelas_id)
            ->pluck('id')
            ->toArray();

        abort_unless(in_array($tugas->guru_mapel_kelas_id, $guruMapelKelasIds), 403);
        abort_unless($tugas->jenis === 'online', 403, 'Hanya tugas online yang dapat dikerjakan di sistem.');

        // Cek apakah sudah mengerjakan (cek jawaban atau nilai selesai)
        $sudahMengerjakan = JawabanTugas::where('tugas_id', $tugas->id)
            ->where('siswa_id', $siswa->id)
            ->exists();

        if ($sudahMengerjakan) {
            return redirect()->route('siswa.tugas.show', $tugas)
                ->with('info', 'Anda sudah mengerjakan tugas ini. Lihat nilai Anda.');
        }

        $soals = $tugas->soalTugas()
            ->select(['id', 'soal', 'opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'bobot'])
            ->get();

        $tugas->load(['materi.bab', 'guruMapelKelas.mapel']);

        $nilai = NilaiTugas::where('tugas_id', $tugas->id)
            ->where('siswa_id', $siswa->id)
            ->first();

        return view('siswa.tugas.kerjakan', compact('tugas', 'soals', 'nilai'));
    }

    /**
     * Siswa submit jawaban tugas online.
     */
    public function tugasSubmit(Request $request, Tugas $tugas)
    {
        $siswa = $this->getSiswa();

        $guruMapelKelasIds = GuruMapelKelas::where('kelas_id', $siswa->kelas_id)
            ->pluck('id')
            ->toArray();

        abort_unless(in_array($tugas->guru_mapel_kelas_id, $guruMapelKelasIds), 403);
        abort_unless($tugas->jenis === 'online', 403, 'Hanya tugas online yang dapat dikumpulkan di sistem.');

        $sudahMengerjakan = JawabanTugas::where('tugas_id', $tugas->id)
            ->where('siswa_id', $siswa->id)
            ->exists();

        if ($sudahMengerjakan) {
            return redirect()->route('siswa.tugas.show', $tugas);
        }

        $request->validate([
            'jawaban' => ['required', 'array'],
            'jawaban.*.soal_id' => ['required', 'exists:soal_tugas,id'],
            'jawaban.*.jawaban' => ['required', 'in:a,b,c,d'],
        ]);

        // Simpan jawaban dan hitung nilai
        $soals = $tugas->soalTugas()->get()->keyBy('id');
        $jumlahBenar = 0;
        $bobotBenar = 0;
        $totalBobot = $soals->sum('bobot');

        $isLate = false;
        if ($tugas->tanggal_deadline && Carbon::now()->isAfter(Carbon::parse($tugas->tanggal_deadline))) {
            $isLate = true;
        }

        foreach ($request->jawaban as $item) {
            $soalId = $item['soal_id'];
            $jawaban = $item['jawaban'];

            if (! $soals->has($soalId)) {
                continue;
            }

            $soal = $soals[$soalId];
            $isBenar = $soal->jawaban_benar === $jawaban;

            if ($isBenar) {
                $jumlahBenar++;
                $bobotBenar += $soal->bobot;
            }

            JawabanTugas::create([
                'tugas_id' => $tugas->id,
                'soal_tugas_id' => $soalId,
                'siswa_id' => $siswa->id,
                'jawaban' => $jawaban,
                'is_benar' => $isBenar,
            ]);
        }

        $nilai = $totalBobot > 0 ? round(($bobotBenar / $totalBobot) * 100, 2) : 0;

        NilaiTugas::updateOrCreate(
            ['tugas_id' => $tugas->id, 'siswa_id' => $siswa->id],
            [
                'nilai' => $nilai,
                'status' => 'selesai',
                'is_late' => $isLate,
                'catatan' => $isLate ? 'Pengumpulan terlambat' : null,
            ]
        );

        return redirect()->route('siswa.tugas.kerjakan', $tugas)
            ->with('success', 'Tugas berhasil dikumpulkan.');
    }
}
