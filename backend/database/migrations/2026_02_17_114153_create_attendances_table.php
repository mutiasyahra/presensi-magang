<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('attendance_date');
            $table->dateTime('clock_in')->nullable();
            $table->dateTime('clock_out')->nullable();
            $table->string('clock_in_photo')->nullable();
            $table->string('clock_out_photo')->nullable();
            $table->decimal('clock_in_lat',10,8)->nullable();
            $table->decimal('clock_in_long',11,8)->nullable();
            $table->decimal('clock_out_lat',10,8)->nullable();
            $table->decimal('clock_out_long',11,8)->nullable();
            $table->text('rencana_kegiatan')->nullable();
            $table->text('progress_kegiatan')->nullable();
            $table->enum('status',['hadir','izin','alpha'])->default('hadir');
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
        Schema::dropIfExists('attendances');
    }
}
