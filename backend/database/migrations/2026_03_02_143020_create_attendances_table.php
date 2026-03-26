<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->date('attendance_date');

            // ===== CLOCK IN =====
            $table->dateTime('clock_in')->nullable();
            $table->string('clock_in_photo')->nullable();
            $table->decimal('clock_in_lat', 10, 8)->nullable();
            $table->decimal('clock_in_long', 11, 8)->nullable();
            $table->text('rencana_kegiatan')->nullable();

            // ===== CLOCK OUT =====
            $table->dateTime('clock_out')->nullable();
            $table->string('clock_out_photo')->nullable();
            $table->decimal('clock_out_lat', 10, 8)->nullable();
            $table->decimal('clock_out_long', 11, 8)->nullable();
            $table->text('progress_kegiatan')->nullable();

            $table->enum('status', ['hadir', 'izin', 'alpha'])->default('hadir');

            $table->timestamps();

            $table->unique(['user_id', 'attendance_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};