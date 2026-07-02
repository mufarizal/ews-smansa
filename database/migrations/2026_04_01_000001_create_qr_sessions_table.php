<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qr_sessions', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->dateTime('jam_batas')->nullable()->comment('Batas waktu absen masuk/pulang');
            $table->dateTime('jam_maksimal')->nullable()->comment('Jam maksimal absen masuk');
            $table->dateTime('generated_at');
            $table->dateTime('expire_at');
            $table->integer('durasi_menit')->default(1440);
            $table->foreignId('dibuat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('kelas_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('jadwal_id')->nullable()->constrained('jadwals')->nullOnDelete();
            $table->foreignId('mapel_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('jenis_sesi', ['harian', 'mapel'])->default('harian');
            $table->enum('tipe', ['masuk', 'pulang'])->default('masuk');
            $table->integer('jumlah_hadir')->default(0);
            $table->boolean('sudah_ditutup')->default(false);
            $table->text('catatan')->nullable();
            $table->string('kode_sesi')->unique()->nullable();
            $table->timestamps();

            $table->index('tanggal');
            $table->index('expire_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qr_sessions');
    }
};
