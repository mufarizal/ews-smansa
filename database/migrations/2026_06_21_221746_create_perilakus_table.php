<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perilakus', function (Blueprint $table) {
            $table->id();
            $table->string('nama_perilaku');
            $table->enum('jenis', ['positif', 'negatif']);
            $table->integer('poin');
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perilakus');
    }
};
