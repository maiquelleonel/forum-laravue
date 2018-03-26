<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateEmailCampaignContactTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_campaign_contact', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('customer_id')->unsigned()->index();
            $table->integer('lead_id')->nullable()->index('lead_id');
            $table->integer('list_id')->nullable()->index('list_id');
            $table->string('list_name')->nullable()->index('list_name');
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
        Schema::drop('email_campaign_contact');
    }
}
