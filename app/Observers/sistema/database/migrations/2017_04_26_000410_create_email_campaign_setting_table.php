<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateEmailCampaignSettingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("email_campaign_setting", function (Blueprint $table) {
            $table->increments("id");
            $table->string("name");
            $table->enum("service", ["Mautic", "ActiveCampaign"]);
            $table->string("auth_type");
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
        Schema::disableForeignKeyConstraints();
        Schema::drop("email_campaign_setting");
    }
}
