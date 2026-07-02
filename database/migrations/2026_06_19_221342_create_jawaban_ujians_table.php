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
        Schema::create('jawaban_ujians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ujian_harian_id')->constrained('ujian_harians')->cascadeOnDelete();
            $table->foreignId('soal_ujian_id')->constrained('soal_ujians')->cascadeOnDelete();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->enum('jawaban', ['a', 'b', 'c', 'd'])->nullable();
            $table->boolean('is_benar')->default(false);
            $table->timestamps();
            $table->unique(['soal_ujian_id', 'siswa_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jawaban_ujians');
    }
};
