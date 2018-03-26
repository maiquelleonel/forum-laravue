<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddErpSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("erp_setting", function(Blueprint $table){
            $table->increments("id");
            $table->string("name");
            $table->string("service");
            $table->string("api_key")->nullable();
            $table->string("username")->nullable();
            $table->string("password")->nullable();
            $table->string("billet_store_id");
            $table->string("credit_card_store_id");
            $table->string("others_store_id");
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
        Schema::drop("erp_setting");
    }
}
