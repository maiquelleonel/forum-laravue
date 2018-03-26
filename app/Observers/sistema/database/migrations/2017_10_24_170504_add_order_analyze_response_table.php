<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddOrderAnalyzeResponseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("order_analyze_response", function(Blueprint $table){
            $table->increments("id");
            $table->integer("order_id")->index();
            $table->string("rule_name");
            $table->string("rule_response");
            $table->integer("batch");
            $table->boolean("status");
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
        Schema::drop("order_analyze_response");
    }
}
