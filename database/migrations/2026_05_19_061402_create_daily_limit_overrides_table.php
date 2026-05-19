<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_limit_overrides', function (Blueprint $table) {
            $table->id();

            // Which member this override is for
            $table->foreignId('member_id')->constrained()->onDelete('cascade');

            // The override limit for this specific date
            $table->integer('limit');

            // The date this override applies to
            $table->date('date');

            $table->timestamps();

            // Only one override per member per day
            $table->unique(['member_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_limit_overrides');
    }
};