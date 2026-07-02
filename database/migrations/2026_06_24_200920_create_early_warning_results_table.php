<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('early_warning_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->foreignId('generated_by')->comment('User ID Guru Bk yang klik Generate SAW')->constrained('users')->cascadeOnDelete();
            $table->decimal('c1_akademik', 5,2)->default(0)->comment('Rata-rata (rata2 tugas + rata2 ujian) / 2');
            $table->decimal('c2_absensi_harian', 5,2)->default(0)->comment('Rata-rata konversi status absen harian');
            $table->decimal('c3_absensi_mapel', 5,2)->default(0)->comment('Rata-rata konversi status absen mapel');
            $table->decimal('c4_perilaku', 5,2)->default(0)->comment('max(0,100 - total_point_negatif)');
            $table->decimal('total_perilaku_negatif', 5,2)->default(0)->comment('Total Akumulasi Point Negatif semester ini');
            $table->decimal('total_perilaku_positif', 5,2)->default(0)->comment('Total Akumulasi Point Positif semester ini');
            $table->decimal('r1_akademik', 6,4)->default(0);
            $table->decimal('r2_absensi_harian', 6,4)->default(0);
            $table->decimal('r3_absensi_mapel', 6,4)->default(0);
            $table->decimal('r4_perilaku', 6,4)->default(0);
            $table->decimal('skor_akhir', 6,4)->default(0);
            $table->enum('kategori', ['aman', 'perhatian', 'binaan']);
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
            $table->unique(['siswa_id', 'semester_id'], 'unique_siswa_semester');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('early_warning_results');
    }
};
