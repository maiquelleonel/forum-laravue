<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddEmailCampaignColumnOnSiteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("site", function (Blueprint $table) {
            $table->integer("email_campaign_setting_id")
                  ->unsigned()
                  ->index()
                  ->nullable()
                  ->after("company_id");

            $table->foreign('email_campaign_setting_id')
                  ->references('id')
                  ->on('email_campaign_setting');
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
        Schema::table("site", function (Blueprint $table) {
            $table->dropForeign('site_email_campaign_setting_id_foreign');
            $table->dropColumn("email_campaign_setting_id");
        });

    }
}
