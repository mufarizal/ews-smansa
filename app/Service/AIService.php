<?php

namespace App\Service;

use App\Models\AiRecommendation;
use App\Models\EarlyWarningResult;
use App\Models\Kelas;
use App\Models\Semester;
use App\Models\Siswa;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    const SYSTEM_PROMPT = <<<'SYSTEM'
Kamu adalah asisten Guru BK (Bimbingan Konseling) SMA yang membantu menganalisis kondisi siswa berdasarkan data Early Warning System (EWS).

Tugasmu: baca data tiap siswa secara individual, identifikasi penyebab masalah dari data yang ada, lalu beri saran tindakan yang spesifik dan actionable untuk Guru BK.

Keterangan data siswa:
- kategori: hasil klasifikasi SAW (binaan/perhatian/aman)
- skor_saw: nilai akhir SAW (0-1), semakin rendah semakin bermasalah
- c1 + r1: nilai akademik (rata-rata tugas & ujian) + nilai ternormalisasi
- c2 + r2: absensi mapel (% kehadiran dari seluruh sesi mapel) + nilai ternormalisasi
- c3 + r3: perilaku (100 - total poin pelanggaran) + nilai ternormalisasi
- poin_positif: akumulasi poin perilaku positif (prestasi, keaktifan)
- poin_negatif: akumulasi poin pelanggaran
- data_tidak_lengkap: true jika salah satu kriteria belum ada data semester ini

Cara identifikasi penyebab:
- Bandingkan nilai r1, r2, r3 antar kriteria
- Kriteria dengan r paling rendah = kontributor masalah terbesar
- Bisa 1 penyebab atau lebih, tergantung data siswa
- Jika data_tidak_lengkap=true, sebutkan bahwa data belum lengkap

Cara memberi saran:
- Saran harus spesifik sesuai kondisi siswa, bukan generik
- Jumlah saran fleksibel 2-4, sesuai kebutuhan
- Gunakan bahasa semi-formal yang mudah dipahami Guru BK
- Untuk kategori binaan: fokus intervensi & pelibatan orang tua
- Untuk kategori perhatian: fokus pemantauan & motivasi
- Untuk kategori aman: fokus pengembangan potensi

Format output WAJIB — JSON array murni, tanpa teks lain, tanpa markdown:
[
  {
    "nis": "101001",
    "nama": "Nama Siswa",
    "penyebab": [
      "Deskripsi penyebab 1 berdasarkan data",
      "Deskripsi penyebab 2 jika ada"
    ],
    "saran": [
      "Saran tindakan spesifik 1",
      "Saran tindakan spesifik 2",
      "Saran tindakan spesifik 3 jika diperlukan"
    ]
  }
]
SYSTEM;

    /**
     * Jeda (detik) antar pemanggilan AI dalam satu kelas (antar kategori).
     * Gemini free tier limit umum: 15 RPM -> aman kalau jeda >= 4-5 detik.
     * Kita pasang 6 detik untuk margin aman.
     */
    private const JEDA_ANTAR_KATEGORI_DETIK = 6;

    private array $providers;

    public function __construct()
    {
        $this->providers = [
            [
                'type' => 'gemini',
                'name' => 'ai_studio_1',
                'key' => config('services.ai_studio.key_1'),
                'model' => config('services.ai_studio.model'),
            ],
            [
                'type' => 'gemini',
                'name' => 'ai_studio_2',
                'key' => config('services.ai_studio.key_2'),
                'model' => config('services.ai_studio.model'),
            ],
            [
                'type' => 'ollama',
                'name' => 'ollama_server',
                'url' => config('services.ollama.url'),
                'model' => config('services.ollama.model'),
            ],
        ];
    }

    // =========================================================================
    //  PUBLIC — dipanggil Scheduler setelah SAW selesai
    // =========================================================================

    /**
     * Generate AI untuk semua kelas aktif.
     * Dipanggil oleh Scheduler setelah SAWService->generateHarian() selesai.
     */
    public function generateHarian(): void
    {
        $semester = Semester::where('is_active', true)->first();

        if (!$semester) {
            Log::warning('[AI] Tidak ada semester aktif, skip generate AI.');
            return;
        }

        $kelasList = Kelas::whereHas('siswas')->get();

        foreach ($kelasList as $kelas) {
            try {
                $this->generateUntukKelas($kelas->id);

                Log::info("[AI] Kelas {$kelas->nama_kelas} berhasil di-generate.");
            } catch (\Throwable $e) {
                Log::error("[AI] Gagal generate kelas {$kelas->nama_kelas}: {$e->getMessage()}");
            }
        }
    }

    /**
     * Generate rekomendasi AI untuk satu kelas.
     * Ambil hasil SAW terbaru (tanggal_hitung terkini).
     */
    public function generateUntukKelas(int $kelasId): array
    {
        $semester = Semester::where('is_active', true)->firstOrFail();
        $kelas = Kelas::findOrFail($kelasId);

        // Ambil tanggal_hitung terbaru untuk kelas ini
        $tanggalTerbaru = EarlyWarningResult::where('kelas_id', $kelasId)
            ->where('semester_id', $semester->id)
            ->max('tanggal_hitung');

        if (!$tanggalTerbaru) {
            throw new \RuntimeException(
                "Belum ada hasil SAW untuk kelas {$kelas->nama_kelas}. Tunggu scheduler SAW jalan."
            );
        }

        // Ambil hasil SAW terbaru
        $hasil = EarlyWarningResult::with('siswa')
            ->where('kelas_id', $kelasId)
            ->where('semester_id', $semester->id)
            ->where('tanggal_hitung', $tanggalTerbaru)
            ->orderBy('skor_akhir', 'asc')
            ->get();

        $output = [];
        $kategoriSudahDiproses = false;

        foreach (['binaan', 'perhatian', 'aman'] as $kategori) {
            $siswas = $hasil->where('kategori', $kategori);

            if ($siswas->isEmpty()) {
                $output[$kategori] = null;
                continue;
            }

            if ($kategoriSudahDiproses) {
                Log::info("[AI] Jeda " . self::JEDA_ANTAR_KATEGORI_DETIK . " detik sebelum kategori berikutnya...");
                sleep(self::JEDA_ANTAR_KATEGORI_DETIK);
            }

            $output[$kategori] = $this->callDenganFallback(
                konteks: "Kelas: {$kelas->nama_kelas} | Semester: {$semester->nama} | Kategori: " . strtoupper($kategori),
                siswas: $siswas,
                scope: 'kelas',
                scopeId: $kelasId,
                kategori: $kategori,
                semesterId: $semester->id,
            );

            $kategoriSudahDiproses = true;
        }

        return $output;
    }

    /**
     * Generate rekomendasi AI untuk satu siswa.
     * Ambil hasil SAW terbaru siswa tersebut.
     */
    public function generateUntukSiswa(int $siswaId): array
    {
        $semester = Semester::where('is_active', true)->firstOrFail();
        $siswa = Siswa::findOrFail($siswaId);

        $row = EarlyWarningResult::with('siswa')
            ->where('siswa_id', $siswaId)
            ->where('semester_id', $semester->id)
            ->orderByDesc('tanggal_hitung')
            ->first();

        if (!$row) {
            throw new \RuntimeException(
                "Data SAW siswa {$siswa->nama} belum ada. Tunggu scheduler SAW jalan."
            );
        }

        return $this->callDenganFallback(
            konteks: "Siswa Individual | Semester: {$semester->nama} | Kategori: " . strtoupper($row->kategori),
            siswas: collect([$row]),
            scope: 'siswa',
            scopeId: $siswaId,
            kategori: $row->kategori,
            semesterId: $semester->id,
        );
    }

    // =========================================================================
    //  PRIVATE — CORE LOGIC
    // =========================================================================

    private function callDenganFallback(
        string $konteks,
        Collection $siswas,
        string $scope,
        int $scopeId,
        string $kategori,
        int $semesterId
    ): array {

        $prompt = $this->buildPrompt($konteks, $siswas);
        $lastException = null;

        foreach ($this->providers as $provider) {

            // Validasi provider Gemini wajib punya API Key
            if (
                $provider['type'] === 'gemini' &&
                empty($provider['key'])
            ) {
                Log::warning("[AI] Key kosong untuk {$provider['name']}, skip.");
                continue;
            }

            try {

                Log::info("[AI] Mencoba {$provider['name']} → {$konteks}");

                switch ($provider['type']) {

                    case 'gemini':

                        $raw = $this->callGemini(
                            $provider['key'],
                            $provider['model'],
                            $prompt
                        );

                        break;

                    case 'ollama':

                        $raw = $this->callOllama(
                            $provider['url'],
                            $provider['model'],
                            $prompt
                        );

                        break;

                    default:

                        throw new \RuntimeException(
                            "Provider AI '{$provider['type']}' tidak dikenali."
                        );
                }

                $parsed = $this->parseResponse($raw);

                $this->simpanRekomendasi(
                    scope: $scope,
                    scopeId: $scopeId,
                    semesterId: $semesterId,
                    kategori: $kategori,
                    rekomendasi: $parsed,
                    providerUsed: $provider['name'],
                );

                Log::info("[AI] Berhasil menggunakan {$provider['name']}");

                return $parsed;

            } catch (\Throwable $e) {

                Log::warning(
                    "[AI] {$provider['name']} gagal: {$e->getMessage()}"
                );

                $lastException = $e;
            }
        }

        throw new \RuntimeException(
            'Semua provider AI gagal. Error terakhir: ' .
            ($lastException?->getMessage() ?? 'Unknown error')
        );
    }
    private function callGemini(string $key, string $model, string $prompt): string
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

        $response = Http::timeout(60)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post("{$url}?key={$key}", [
                'contents' => [
                    [
                        'parts' => [['text' => $prompt]],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0.3,
                    'maxOutputTokens' => 8192,
                ],
            ]);

        if ($response->status() === 429 || $response->serverError()) {
            throw new \RuntimeException("HTTP {$response->status()}: " . $response->body());
        }

        if ($response->failed()) {
            throw new \RuntimeException("HTTP {$response->status()}: " . $response->body());
        }

        $text = $response->json('candidates.0.content.parts.0.text');

        if (empty($text)) {
            throw new \RuntimeException('Response kosong dari Gemini.');
        }

        return $text;
    }

    private function callOllama(
        string $url,
        string $model,
        string $prompt
    ): string {
        $response = Http::timeout(60)
            ->acceptJson()
            ->post(rtrim($url, '/') . '/api/generate', [
                'model' => $model,
                'prompt' => $prompt,
                'stream' => false,
            ]);

        if ($response->failed()) {
            throw new \RuntimeException(
                "HTTP {$response->status()}: {$response->body()}"
            );
        }

        $text = $response->json('response');

        if (empty($text)) {
            throw new \RuntimeException('Response kosong dari Ollama.');
        }

        return $text;
    }

    private function buildPrompt(string $konteks, Collection $siswas): string
    {
        $dataSiswa = $siswas->map(fn($row) => [
            'nis' => $row->siswa->nis ?? '-',
            'nama' => $row->siswa->nama ?? '-',
            'kategori' => $row->kategori,
            'skor_saw' => $row->skor_akhir,
            // C1 — Akademik
            'c1' => $row->c1_akademik,
            'r1' => $row->r1_akademik,
            // C2 — Absensi Mapel
            'c2' => $row->c2_absensi,
            'r2' => $row->r2_absensi,
            // C3 — Perilaku
            'c3' => $row->c3_perilaku,
            'r3' => $row->r3_perilaku,
            // Konteks tambahan untuk AI
            'poin_positif' => $row->total_perilaku_positif,
            'poin_negatif' => $row->total_perilaku_negatif,
            'data_tidak_lengkap' => $row->data_tidak_lengkap,
        ])->values()->toJson(JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return self::SYSTEM_PROMPT
            . "\n\n---\n"
            . "Konteks: {$konteks}\n\n"
            . "Data Siswa:\n{$dataSiswa}";
    }

    private function parseResponse(string $raw): array
    {
        // Bersihkan markdown fence jika ada
        $clean = preg_replace('/^```(?:json)?\s*/i', '', trim($raw));
        $clean = preg_replace('/\s*```$/', '', $clean);
        $clean = trim($clean);

        $decoded = json_decode($clean, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            Log::error('[AI] Gagal parse JSON response', [
                'raw' => substr($raw, 0, 500),
                'error' => json_last_error_msg(),
            ]);
            throw new \RuntimeException('Response AI tidak valid: ' . json_last_error_msg());
        }

        return $decoded;
    }

    private function simpanRekomendasi(
        string $scope,
        int $scopeId,
        int $semesterId,
        string $kategori,
        array $rekomendasi,
        string $providerUsed
    ): void {
        AiRecommendation::updateOrCreate(
            [
                'scope' => $scope,
                'scope_id' => $scopeId,
                'semester_id' => $semesterId,
                'kategori' => $kategori,
            ],
            [
                'rekomendasi' => $rekomendasi,
                'provider_used' => $providerUsed,
                'generated_at' => now(),
            ]
        );
    }
}