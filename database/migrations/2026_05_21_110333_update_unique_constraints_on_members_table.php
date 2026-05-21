<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            // Safely drop no_ktp unique only if it exists
            $indexes = DB::select("SHOW INDEX FROM members WHERE Key_name = 'members_no_ktp_unique'");
            if (!empty($indexes)) {
                $table->dropUnique(['no_ktp']);
            }

            // Add unique on nama only if it doesn't exist
            $namaIndex = DB::select("SHOW INDEX FROM members WHERE Key_name = 'members_nama_unique'");
            if (empty($namaIndex)) {
                $table->unique('nama');
            }

            // Add composite unique on unit+kawasan only if it doesn't exist
            $unitIndex = DB::select("SHOW INDEX FROM members WHERE Key_name = 'members_unit_kawasan_unique'");
            if (empty($unitIndex)) {
                $table->unique(['unit', 'kawasan']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropUnique(['nama']);
            $table->dropUnique(['unit', 'kawasan']);
            $table->unique('no_ktp');
        });
    }
};