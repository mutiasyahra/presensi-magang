<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeavesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leaves', function (Blueprint $table) {
            $table->id();

            // Relasi ke user
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Tanggal izin/sakit
            $table->date('date');

            // Jenis izin
            $table->enum('type', ['izin', 'sakit']);

            // Alasan
            $table->text('reason');

            // File bukti (opsional)
            $table->string('file')->nullable();

            // Status approval
            $table->enum('status', ['pending', 'approved', 'rejected'])
                ->default('pending');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('leaves');
    }
}
