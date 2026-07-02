<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ujian_harians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_mapel_kelas_id')->constrained('guru_mapel_kelas')->cascadeOnDelete();
            $table->foreignId('bab_id')->constrained('babs')->cascadeOnDelete();
            $table->string('judul');
            $table->date('tanggal_ujian');
            $table->integer('durasi_menit')->default(60);
            $table->enum('status', ['draft', 'publish', 'selesai'])->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ujian_harians');
    }
};
