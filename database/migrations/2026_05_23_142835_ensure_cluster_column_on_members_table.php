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
            // Add cluster column only if it doesn't exist
            if (!Schema::hasColumn('members', 'cluster')) {
                $table->string('cluster')->nullable()->after('unit');
            }

            // Drop old unique constraints safely
            $indexes = DB::select("SHOW INDEX FROM members");
            $indexNames = collect($indexes)->pluck('Key_name')->toArray();

            if (in_array('members_unit_kawasan_unique', $indexNames)) {
                $table->dropUnique(['unit', 'kawasan']);
            }

            if (in_array('members_unit_accluster_kawasan_unique', $indexNames)) {
                $table->dropUnique(['unit', 'accluster', 'kawasan']);
            }

            if (in_array('members_unit_cluster_kawasan_unique', $indexNames)) {
                $table->dropUnique(['unit', 'cluster', 'kawasan']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            if (Schema::hasColumn('members', 'cluster')) {
                $table->dropColumn('cluster');
            }
        });
    }
};