<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            $table->foreignId('bab_id')->nullable()->constrained('babs')->nullOnDelete()->after('jadwal_id');
            $table->foreignId('materi_id')->nullable()->constrained('materis')->nullOnDelete()->after('bab_id');
            $table->string('topik_pembelajaran')->nullable()->after('materi_id');
        });
    }

    public function down(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            $table->dropForeign(['materi_id']);
            $table->dropColumn('materi_id');

            $table->dropForeign(['bab_id']);
            $table->dropColumn('bab_id');

            $table->dropColumn('topik_pembelajaran');
        });
    }
};
