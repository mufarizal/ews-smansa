<?php

namespace App\Console\Commands;

use App\Models\Kelas;
use App\Models\Semester;
use App\Service\AIService;
use App\Service\SAWService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class HitungEWSHarian extends Command
{
    protected $signature = 'ews:hitung-harian
                                {--saw-only : Hanya jalankan SAW, skip AI}
                                {--ai-only  : Hanya jalankan AI (SAW harus sudah jalan)}
                                {--kelas=   : Hanya hitung kelas tertentu (isi kelas_id)}
                                {--tanggal= : Tanggal hitung (YYYY-MM-DD). Default: hari ini}
                                {--from=    : Tanggal mulai backfill (YYYY-MM-DD)}
                                {--to=      : Tanggal akhir backfill (YYYY-MM-DD). Default: hari ini}
                                {--backfill : Backfill tanggal yang belum ada EWR sejak awal semester}';

    protected $description = 'Hitung SAW dan generate rekomendasi AI untuk semua kelas secara otomatis.';

    public function __construct(
        private SAWService $sawService,
        private AIService $aiService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $semester = Semester::where('is_active', true)->first();

        if (! $semester) {
            $this->error('Tidak ada semester aktif. Batalkan.');

            return self::FAILURE;
        }

        $backfill = $this->option('backfill');
        $tanggal = $this->option('tanggal');
        $from = $this->option('from');
        $to = $this->option('to');

        if ($backfill) {
            return $this->handleBackfill($semester);
        }

        if ($from && $to) {
            return $this->handleRange($semester, $from, $to);
        }

        $tanggal ??= Carbon::today()->toDateString();
        $sawOnly = $this->option('saw-only');
        $aiOnly = $this->option('ai-only');
        $filterKelas = $this->option('kelas');

        $this->info("=== EWS Harian [{$tanggal}] | Semester: {$semester->nama} ===");

        $query = Kelas::whereHas('siswas');
        if ($filterKelas) {
            $query->where('id', (int) $filterKelas);
        }
        $kelasList = $query->get();

        if ($kelasList->isEmpty()) {
            $this->warn('Tidak ada kelas dengan siswa. Selesai.');

            return self::SUCCESS;
        }

        $this->info("Total kelas: {$kelasList->count()}");

        if (! $aiOnly) {
            $this->info("\n[SAW] Mulai perhitungan...");
            $this->newLine();

            $bar = $this->output->createProgressBar($kelasList->count());
            $bar->start();

            foreach ($kelasList as $kelas) {
                try {
                    $this->sawService->generateUntukKelas(
                        kelasId: $kelas->id,
                        semester: $semester,
                        tanggalHitung: $tanggal,
                    );
                } catch (\Throwable $e) {
                    Log::error("[SAW] Gagal kelas {$kelas->nama_kelas}: {$e->getMessage()}");
                    $this->warn("\n[SAW] Gagal: {$kelas->nama_kelas} — {$e->getMessage()}");
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);
            $this->info('[SAW] Selesai.');
        }

        if (! $sawOnly) {
            $this->info("\n[AI] Mulai generate rekomendasi...");
            $this->info('[AI] Jeda 10 detik antar kelas untuk menghindari rate limit.');
            $this->newLine();

            foreach ($kelasList as $index => $kelas) {
                if ($index > 0) {
                    $this->info('[AI] Menunggu 10 detik sebelum kelas berikutnya...');
                    sleep(10);
                }

                $this->info("[AI] Generate: {$kelas->nama_kelas}");

                try {
                    $this->aiService->generateUntukKelas($kelas->id);
                    $this->info("[AI] ✓ {$kelas->nama_kelas} selesai.");
                } catch (\Throwable $e) {
                    Log::error("[AI] Gagal kelas {$kelas->nama_kelas}: {$e->getMessage()}");
                    $this->warn("[AI] Gagal: {$kelas->nama_kelas} — {$e->getMessage()}");
                }
            }

            $this->newLine();
            $this->info('[AI] Selesai.');
        }

        $this->newLine();
        $this->info("=== EWS Harian selesai [{$tanggal}] ===");

        Log::info('[EWS] Hitung harian selesai', [
            'tanggal' => $tanggal,
            'semester' => $semester->nama,
            'jumlah_kelas' => $kelasList->count(),
        ]);

        return self::SUCCESS;
    }

    private function handleBackfill(Semester $semester): int
    {
        $tanggalMulai = Carbon::parse($semester->tanggal_mulai);
        $tanggalSekarang = Carbon::today();
        $tanggalAkhir = Carbon::parse($this->option('to') ?: $tanggalSekarang->toDateString());

        if ($tanggalAkhir->greaterThan($tanggalSekarang)) {
            $tanggalAkhir = $tanggalSekarang;
        }

        $this->info("=== Backfill EWS | Semester: {$semester->nama} ===");
        $this->info("Periode: {$tanggalMulai->format('Y-m-d')} sampai {$tanggalAkhir->format('Y-m-d')}");

        $kelasList = Kelas::whereHas('siswas')->get();
        if ($kelasList->isEmpty()) {
            $this->warn('Tidak ada kelas dengan siswa.');

            return self::SUCCESS;
        }

        $totalBatch = 0;
        $totalSukses = 0;
        $totalGagal = 0;

        foreach ($kelasList as $kelas) {
            $tanggal = $tanggalMulai->copy();

            while ($tanggal->lessThanOrEqualTo($tanggalAkhir)) {
                if ((int) $tanggal->format('N') < 6) {
                    $tanggalStr = $tanggal->format('Y-m-d');
                    $totalBatch++;

                    try {
                        $this->sawService->generateUntukKelas(
                            kelasId: $kelas->id,
                            semester: $semester,
                            tanggalHitung: $tanggalStr,
                        );
                        $totalSukses++;
                    } catch (\Throwable $e) {
                        $totalGagal++;
                        $this->warn("[SAW] Gagal: {$kelas->nama_kelas} {$tanggalStr} — {$e->getMessage()}");
                        Log::error("[SAW] Backfill gagal: {$kelas->nama_kelas} {$tanggalStr}: {$e->getMessage()}");
                    }
                }

                $tanggal->addDay();
            }
        }

        $this->newLine(2);
        $this->info('=== Hasil Backfill ===');
        $this->info("Total batch: {$totalBatch}");
        $this->info("Sukses: {$totalSukses}");
        $this->info("Gagal: {$totalGagal}");

        return self::SUCCESS;
    }

    private function handleRange(Semester $semester, string $from, string $to): int
    {
        $tanggalMulai = Carbon::parse($from);
        $tanggalAkhir = Carbon::parse($to);

        if ($tanggalMulai->greaterThan($tanggalAkhir)) {
            [$tanggalMulai, $tanggalAkhir] = [$tanggalAkhir, $tanggalMulai];
        }

        $this->info("=== Backfill Rentang | Semester: {$semester->nama} ===");
        $this->info("Periode: {$tanggalMulai->format('Y-m-d')} sampai {$tanggalAkhir->format('Y-m-d')}");

        $kelasList = Kelas::whereHas('siswas')->get();
        if ($kelasList->isEmpty()) {
            $this->warn('Tidak ada kelas dengan siswa.');

            return self::SUCCESS;
        }

        $totalBatch = 0;
        $totalSukses = 0;
        $totalGagal = 0;

        foreach ($kelasList as $kelas) {
            $tanggal = $tanggalMulai->copy();

            while ($tanggal->lessThanOrEqualTo($tanggalAkhir)) {
                if ((int) $tanggal->format('N') < 6) {
                    $tanggalStr = $tanggal->format('Y-m-d');
                    $totalBatch++;

                    try {
                        $this->sawService->generateUntukKelas(
                            kelasId: $kelas->id,
                            semester: $semester,
                            tanggalHitung: $tanggalStr,
                        );
                        $totalSukses++;
                    } catch (\Throwable $e) {
                        $totalGagal++;
                        $this->warn("[SAW] Gagal: {$kelas->nama_kelas} {$tanggalStr} — {$e->getMessage()}");
                        Log::error("[SAW] Range gagal: {$kelas->nama_kelas} {$tanggalStr}: {$e->getMessage()}");
                    }
                }

                $tanggal->addDay();
            }
        }

        $this->newLine(2);
        $this->info('=== Hasil Backfill ===');
        $this->info("Total batch: {$totalBatch}");
        $this->info("Sukses: {$totalSukses}");
        $this->info("Gagal: {$totalGagal}");

        return self::SUCCESS;
    }
}
