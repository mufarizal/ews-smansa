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
                                {--kelas=   : Hanya hitung kelas tertentu (isi kelas_id)}';

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

        if (!$semester) {
            $this->error('Tidak ada semester aktif. Batalkan.');
            return self::FAILURE;
        }

        $tanggal = Carbon::today()->toDateString();
        $sawOnly = $this->option('saw-only');
        $aiOnly = $this->option('ai-only');
        $filterKelas = $this->option('kelas');

        $this->info("=== EWS Harian [{$tanggal}] | Semester: {$semester->nama} ===");

        // Ambil daftar kelas
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

        // =====================================================================
        //  STEP 1 — SAW
        // =====================================================================

        if (!$aiOnly) {
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

        // =====================================================================
        //  STEP 2 — AI
        //  Kasih jeda antar kelas agar tidak kena rate limit Gemini free tier.
        //  Default: 10 detik antar kelas (aman untuk 15 RPM limit).
        // =====================================================================

        if (!$sawOnly) {
            $this->info("\n[AI] Mulai generate rekomendasi...");
            $this->info('[AI] Jeda 10 detik antar kelas untuk menghindari rate limit.');
            $this->newLine();

            foreach ($kelasList as $index => $kelas) {
                // Jeda sebelum kelas ke-2 dan seterusnya
                if ($index > 0) {
                    $this->info("[AI] Menunggu 10 detik sebelum kelas berikutnya...");
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
}