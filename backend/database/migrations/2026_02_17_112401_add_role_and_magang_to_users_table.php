<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRoleAndMagangToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('tempat_magang')->nullable();
            $table->string('alamat_magang')->nullable();
            $table->decimal('latitude_magang',10,8)->nullable();
            $table->decimal('longitude_magang',11,8)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'tempat_magang',
                'alamat_magang',
                'latitude_magang',
                'longitude_magang'
            ]);
        });
    }
}
