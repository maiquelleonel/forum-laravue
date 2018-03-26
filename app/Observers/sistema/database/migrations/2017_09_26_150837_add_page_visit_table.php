<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddPageVisitTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("page_visit", function(Blueprint $table){
            $table->increments("id");
            $table->string("visitor_id")->index();
            $table->integer("customer_id")->index()->nullable();

            $table->string("utm_source")->nullable();
            $table->string("utm_medium")->nullable();
            $table->string("utm_campaign")->nullable();
            $table->string("utm_term")->nullable();
            $table->string("utm_content")->nullable();

            $table->string("referrer")->nullable();

            $table->string("custom_var_k1")->nullable();
            $table->string("custom_var_v1")->nullable();
            $table->string("custom_var_k2")->nullable();
            $table->string("custom_var_v2")->nullable();
            $table->string("custom_var_k3")->nullable();
            $table->string("custom_var_v3")->nullable();
            $table->string("custom_var_k4")->nullable();
            $table->string("custom_var_v4")->nullable();
            $table->string("custom_var_k5")->nullable();
            $table->string("custom_var_v5")->nullable();

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
        Schema::drop("page_visit");
    }
}
