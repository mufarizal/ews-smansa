<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gurus', function (Blueprint $table) {
            $table->dropColumn(['type', 'piket_days']);
        });
    }

    public function down(): void
    {
        Schema::table('gurus', function (Blueprint $table) {
            $table->enum('type', ['mapel', 'piket', 'bk'])->nullable()->after('no_hp');
            $table->json('piket_days')->nullable()->after('type');
        });
    }
};
