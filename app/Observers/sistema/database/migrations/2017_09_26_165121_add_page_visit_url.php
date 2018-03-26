<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddPageVisitUrl extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("page_visit_url", function(Blueprint $table){
            $table->increments("id");
            $table->string("full_url");
            $table->string("prefix");
            $table->string("domain");
            $table->string("query")->nullable();
            $table->string("path")->nullable();
            $table->integer("page_visit_id");
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
        Schema::drop("page_visit_url");
    }
}
