<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entry_logs', function (Blueprint $table) {
            // Auto-incrementing primary key
            $table->id();

            // Which member this log belongs to
            // If the member is deleted, their logs are also deleted
            $table->foreignId('member_id')->constrained()->onDelete('cascade');

            // Which gate/device scanned the QR
            $table->string('gate_name')->default('Main Gate');

            // Whether entry was allowed or denied
            $table->enum('status', ['granted', 'denied']);

            // Why entry was denied (null if granted)
            // e.g. "Member inactive" or "Batas akses harian telah tercapai. (4/4)"
            $table->string('deny_reason')->nullable();

            // When the scan happened
            $table->timestamp('scanned_at')->useCurrent();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entry_logs');
    }
};