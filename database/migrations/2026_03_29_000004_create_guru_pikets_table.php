<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('guru_pikets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->constrained('gurus')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->string('hari')->comment('Hari piket');
            $table->text('catatan')->nullable()->comment('Catatan admin');
            $table->timestamps();

            $table->index(['guru_id', 'hari']);
            // $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guru_pikets');
    }
};
