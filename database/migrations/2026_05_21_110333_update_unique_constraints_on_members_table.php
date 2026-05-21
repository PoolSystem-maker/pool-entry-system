<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            // Remove old unique constraint on no_ktp
            $table->dropUnique(['no_ktp']);

            // Add unique on nama
            $table->unique('nama');

            // Add composite unique on unit + kawasan
            // A-23 PALACE and A-23 PAVILION are different
            $table->unique(['unit', 'kawasan']);
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