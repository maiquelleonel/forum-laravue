<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddClickIdColumnOnTrackableTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('customers', function (Blueprint $table) {
            $table->string('click_id')->nullable()->after('site_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('click_id')->nullable()->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('click_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('click_id');
        });
    }
}
