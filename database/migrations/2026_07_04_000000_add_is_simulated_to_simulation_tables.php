<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            $table->boolean('is_simulated')->default(false)->after('sudah_disetujui');
        });

        Schema::table('nilai_tugas', function (Blueprint $table) {
            $table->boolean('is_simulated')->default(false)->after('catatan');
        });

        Schema::table('hasil_ujians', function (Blueprint $table) {
            $table->boolean('is_simulated')->default(false)->after('nilai');
        });

        Schema::table('perilaku_siswas', function (Blueprint $table) {
            $table->boolean('is_simulated')->default(false)->after('catatan');
        });
    }

    public function down(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            $table->dropColumn('is_simulated');
        });

        Schema::table('nilai_tugas', function (Blueprint $table) {
            $table->dropColumn('is_simulated');
        });

        Schema::table('hasil_ujians', function (Blueprint $table) {
            $table->dropColumn('is_simulated');
        });

        Schema::table('perilaku_siswas', function (Blueprint $table) {
            $table->dropColumn('is_simulated');
        });
    }
};