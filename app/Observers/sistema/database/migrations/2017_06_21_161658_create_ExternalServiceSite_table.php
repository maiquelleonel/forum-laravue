<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExternalServiceSiteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("external_service_settings_site", function(Blueprint $table){
            $table->increments("id");
            $table->integer("site_id")->unsigned();
            $table->integer("external_service_settings_id")->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop("external_service_settings_site");
    }
}
