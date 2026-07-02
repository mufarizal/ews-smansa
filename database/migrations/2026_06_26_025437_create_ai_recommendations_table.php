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
        Schema::create('ai_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->enum('scope', ['kelas', 'siswa']);
            $table->unsignedBigInteger('scope_id');
            $table->enum('kategori', ['binaan', 'perhatian', 'aman']);
            $table->json('rekomendasi');
            $table->string('provider_used')->default('ai_studio_1')->comment('ai_studio_1, ai_studio_2');
            $table->timestamp('generated_at')->nullable();
            $table->unique(['scope', 'scope_id', 'semester_id', 'kategori'], 'unique_scope_semester_kategori');
            $table->index(['scope', 'scope_id', 'semester_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_recommendations');
    }
};
