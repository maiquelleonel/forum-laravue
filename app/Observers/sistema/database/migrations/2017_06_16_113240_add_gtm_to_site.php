<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddGtmToSite extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("site", function(Blueprint $table){
            $table->string("gtm_code")->nullable()->after("bundle_group_id");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("site", function(Blueprint $table){
            $table->dropColumn("gtm_code");
        });
    }
}
