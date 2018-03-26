<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddClickidColumnToVisit extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        Schema::table('page_visit', function (Blueprint $table) {
            $table->string("click_id")->nullable()->index()->after("custom_var_v5");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('page_visit', function (Blueprint $table) {
            $table->dropColumn("click_id");
        });
    }
}
