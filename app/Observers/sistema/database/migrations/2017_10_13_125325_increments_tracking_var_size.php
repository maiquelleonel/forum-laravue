<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class IncrementsTrackingVarSize extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("page_visit_url", function(Blueprint $table){
            $table->string("full_url", 500)->change();
            $table->string("query", 500)->change();
        });
        Schema::table("page_visit", function(Blueprint $table){
            $table->string("referrer", 500)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("page_visit_url", function(Blueprint $table){
            $table->string("full_url")->change();
            $table->string("query")->change();
        });
        Schema::table("page_visit", function(Blueprint $table){
            $table->string("referrer")->change();
        });
    }
}
