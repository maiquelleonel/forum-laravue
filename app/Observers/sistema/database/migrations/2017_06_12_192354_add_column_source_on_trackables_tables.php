<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnSourceOnTrackablesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('source')->nullable()->after('click_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('source')->nullable()->after('click_id');
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
            $table->dropColumn('source');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
}
