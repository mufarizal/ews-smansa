<?php

namespace App\Imports;

use App\Models\Guru;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class GuruImport implements ToCollection, WithHeadingRow
{
    private const DEFAULT_EMAIL_DOMAIN = 'sma.com';
    private const DEFAULT_PASSWORD = 'default$123';

    private int $successCount = 0;
    private int $failureCount = 0;
    private array $failures = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;

            try {
                $this->validateRow($row, $rowNumber);

                $nip = $this->normalizeNip((string) $row['nip']);
                $email = $nip . '@' . self::DEFAULT_EMAIL_DOMAIN;

                if (Guru::where('nip', $nip)->exists()) {
                    $this->addFailure($rowNumber, "NIP {$nip} sudah terdaftar");
                    continue;
                }

                if (User::where('email', $email)->exists()) {
                    $this->addFailure($rowNumber, "Email {$email} sudah terdaftar");
                    continue;
                }

                DB::transaction(function () use ($row, $nip, $email) {
                    $user = User::create([
                        'name' => trim($row['nama']),
                        'email' => $email,
                        'password' => Hash::make(self::DEFAULT_PASSWORD),
                        'default_role' => null, // konsisten dengan GuruController
                    ]);

                    Guru::create([
                        'user_id' => $user->id,
                        'nip' => $nip,
                        'nama' => trim($row['nama']),
                        'no_hp' => isset($row['no_hp']) && $row['no_hp'] !== ''
                            ? trim((string) $row['no_hp'])
                            : null,
                    ]);
                });

                $this->successCount++;

            } catch (\Throwable $e) {
                $this->addFailure($rowNumber, $e->getMessage());
            }
        }
    }

    private function validateRow($row, int $rowNumber): void
    {
        $nip = trim((string) ($row['nip'] ?? ''));
        $nama = trim((string) ($row['nama'] ?? ''));

        if ($nip === '') {
            throw new \Exception("Baris {$rowNumber}: NIP tidak boleh kosong");
        }

        if ($nama === '') {
            throw new \Exception("Baris {$rowNumber}: Nama tidak boleh kosong");
        }

        // Bersihkan dulu dari kemungkinan float Excel sebelum validasi panjang
        $nipClean = $this->normalizeNip($nip);

        if (strlen($nipClean) < 6) {
            throw new \Exception("Baris {$rowNumber}: NIP minimal 6 karakter (dapat: '{$nipClean}')");
        }

        if (!preg_match('/^[0-9]+$/', $nipClean)) {
            throw new \Exception("Baris {$rowNumber}: NIP hanya boleh berisi angka (dapat: '{$nipClean}')");
        }
    }

    private function normalizeNip(string $nip): string
    {
        $nip = trim($nip);

        // Tangani format email yang masuk
        if (str_contains($nip, '@')) {
            $nip = explode('@', $nip)[0];
        }

        // Hapus semua spasi (format NIP ASN: "19721021 202121 1 002")
        $nip = str_replace(' ', '', $nip);

        // Tangani float dari Excel: "123001.0" → "123001"
        if (str_contains($nip, '.')) {
            $nip = (string) (int) $nip;
        }

        return $nip;
    }

    private function addFailure(int $row, string $error): void
    {
        $this->failures[] = ['row' => $row, 'error' => $error];
        $this->failureCount++;
    }

    public function getSuccessCount(): int
    {
        return $this->successCount;
    }
    public function getFailureCount(): int
    {
        return $this->failureCount;
    }
    public function getFailures(): array
    {
        return $this->failures;
    }

    // backward compat jika ada kode lain yang panggil getErrors()
    public function getErrors(): array
    {
        return $this->failures;
    }
}