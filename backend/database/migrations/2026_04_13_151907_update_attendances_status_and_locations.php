<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateAttendancesStatusAndLocations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('clock_in_location')->nullable()->after('clock_in_long');
            $table->string('clock_out_location')->nullable()->after('clock_out_long');
        });

        // Update enum status using raw SQL as standard enum change in Laravel is tricky
        DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('hadir', 'izin', 'alpha', 'terlambat') DEFAULT 'hadir'");
    }

    public function down()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['clock_in_location', 'clock_out_location']);
        });

        DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('hadir', 'izin', 'alpha') DEFAULT 'hadir'");
    }
}
