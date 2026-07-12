<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL tidak bisa drop index yang kolom-nya dipakai FK
        // Solusi: drop FK dulu → drop index → recreate FK → tambah index baru

        // Step 1: Drop FK yang pakai siswa_id
        DB::statement('ALTER TABLE early_warning_results DROP FOREIGN KEY early_warning_results_siswa_id_foreign');

        // Step 2: Baru bisa drop unique index
        DB::statement('ALTER TABLE early_warning_results DROP INDEX unique_siswa_semester');

        // Step 3: Recreate FK siswa_id
        DB::statement('ALTER TABLE early_warning_results ADD CONSTRAINT early_warning_results_siswa_id_foreign FOREIGN KEY (siswa_id) REFERENCES siswas(id) ON DELETE CASCADE');

        // Step 4: Tambah unique constraint baru (support snapshot harian)
        DB::statement('ALTER TABLE early_warning_results ADD UNIQUE KEY unique_siswa_semester_tanggal (siswa_id, semester_id, tanggal_hitung)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE early_warning_results DROP FOREIGN KEY early_warning_results_siswa_id_foreign');
        DB::statement('ALTER TABLE early_warning_results DROP INDEX unique_siswa_semester_tanggal');
        DB::statement('ALTER TABLE early_warning_results ADD CONSTRAINT early_warning_results_siswa_id_foreign FOREIGN KEY (siswa_id) REFERENCES siswas(id) ON DELETE CASCADE');
        DB::statement('ALTER TABLE early_warning_results ADD UNIQUE KEY unique_siswa_semester (siswa_id, semester_id)');
    }
};
