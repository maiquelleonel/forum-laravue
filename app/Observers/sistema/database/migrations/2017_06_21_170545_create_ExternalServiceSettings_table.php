<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExternalServiceSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("external_service_settings", function (Blueprint $table) {
            $table->increments("id");
            $table->string("name");
            $table->string("service");
            $table->string("auth_type")->nullable();
            $table->string("username")->nullable();
            $table->string("password")->nullable();
            $table->string("api_key")->nullable();
            $table->string("oauth_secret_key")->nullable();
            $table->string("oauth_client_key")->nullable();
            $table->string("base_url");
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
        Schema::drop("external_service_settings");
    }
}
