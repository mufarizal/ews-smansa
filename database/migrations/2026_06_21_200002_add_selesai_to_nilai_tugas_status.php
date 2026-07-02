<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nilai_tugas', function (Blueprint $table) {
            $table->enum('status', ['mengerjakan', 'tidak_mengerjakan', 'selesai'])->default('mengerjakan')->change();
        });
    }

    public function down(): void
    {
        Schema::table('nilai_tugas', function (Blueprint $table) {
            $table->enum('status', ['mengerjakan', 'tidak_mengerjakan'])->default('mengerjakan')->change();
        });
    }
};
