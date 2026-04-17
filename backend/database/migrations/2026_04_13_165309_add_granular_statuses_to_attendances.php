<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGranularStatusesToAttendances extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->enum('clock_in_status', ['tepat waktu', 'terlambat'])->nullable()->after('clock_in_location');
            $table->enum('clock_out_status', ['tepat waktu', 'terlalu cepat', 'terlambat'])->nullable()->after('clock_out_location');
        });
    }

    public function down()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['clock_in_status', 'clock_out_status']);
        });
    }
}
