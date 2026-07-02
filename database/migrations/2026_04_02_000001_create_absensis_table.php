<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained()->cascadeOnDelete();
            $table->date('tanggal');
            $table->enum('tipe', ['harian', 'mapel']);
            $table->foreignId('jadwal_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('guru_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('mapel_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['hadir', 'izin', 'sakit', 'alpha', 'terlambat'])->nullable();
            $table->timestamp('jam_masuk')->nullable();
            $table->timestamp('jam_pulang')->nullable();
            $table->integer('terlambat_menit')->default(0);
            $table->string('ip_address')->nullable();
            $table->string('device_id')->nullable();
            $table->decimal('latitude', 10, 8)->nullable()->comment('Latitude GPS');
            $table->decimal('longitude', 11, 8)->nullable()->comment('Longitude GPS');
            $table->integer('akurasi_meter')->nullable()->comment('Akurasi GPS dalam meter');
            $table->integer('distance_meter')->nullable()->comment('Jarak dari sekolah dalam meter');
            $table->foreignId('qr_session_id')->nullable()->constrained('qr_sessions')->nullOnDelete();
            $table->boolean('sudah_disetujui')->default(true);
            $table->text('catatan_persetujuan')->nullable();
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['siswa_id', 'tanggal', 'tipe', 'device_id'], 'unique_siswa_hari_device');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensis');
    }
};
