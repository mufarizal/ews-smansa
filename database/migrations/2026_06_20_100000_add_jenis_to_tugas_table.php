<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tugas', function (Blueprint $table) {
            $table->enum('jenis', ['online', 'offline'])->default('offline')->after('tanggal_deadline');
            $table->string('link_meeting')->nullable()->after('jenis');
        });
    }

    public function down(): void
    {
        Schema::table('tugas', function (Blueprint $table) {
            $table->dropColumn(['jenis', 'link_meeting']);
        });
    }
};
