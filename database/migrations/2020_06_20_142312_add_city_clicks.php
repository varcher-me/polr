<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCityClicks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clicks', function (Blueprint $table) {
            //
            $table->string('city', 60);
            $table->string('province', 60);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clicks', function (Blueprint $table) {
            //
            $table->dropColumn('city');
            $table->dropColumn('province');
        });
    }
}
