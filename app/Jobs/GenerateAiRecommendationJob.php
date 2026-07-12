<?php

namespace App\Jobs;

use App\Service\AIService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GenerateAiRecommendationJob implements ShouldQueue
{
    use Queueable;

    private int $siswaId;

    public function __construct(int $siswaId)
    {
        $this->siswaId = $siswaId;
    }

    public function handle(AIService $aiService): void
    {
        try {
            $aiService->generateUntukSiswa($this->siswaId);
        } catch (\Throwable $e) {
            Log::error('[AI Job] Gagal generate rekomendasi siswa', [
                'siswa_id' => $this->siswaId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
