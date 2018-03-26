<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropEmailCampaignForeign extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("site", function (Blueprint $table) {
            $table->dropForeign('site_email_campaign_setting_id_foreign');
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
            $table->foreign('email_campaign_setting_id')
                ->references('id')
                ->on('email_campaign_setting');
        });
    }
}
