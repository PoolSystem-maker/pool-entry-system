<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            // Auto-incrementing primary key
            $table->id();

            // Owner's full name e.g. "HALBERG"
            $table->string('nama');

            // National ID number — must be unique per person
            $table->string('no_ktp')->unique();

            // Phone number
            $table->string('no_telp');

            // Unit number e.g. "A-8", "B-7"
            $table->string('unit');

            // Area/zone e.g. "PALACE", "PAVILION"
            $table->string('kawasan');

            // Unique token embedded in the QR code
            $table->string('qr_token')->unique();

            // Whether this member is allowed entry (true = active)
            $table->boolean('is_active')->default(true);

            // Max entries allowed per day (default 4, adjustable per member)
            $table->integer('daily_limit')->default(4);

            // Auto-managed created_at and updated_at timestamps
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};