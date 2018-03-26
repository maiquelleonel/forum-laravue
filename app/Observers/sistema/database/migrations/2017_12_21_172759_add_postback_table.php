<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddPostbackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("postback", function(Blueprint $table){
            $table->increments("id");
            $table->string("url", 500);
            $table->integer("site_id");
            $table->integer("user_id")->unsigned();
            $table->enum("method", ["POST", "GET"]);
            $table->timestamps();

            $table->foreign("site_id")
                  ->references("id")
                  ->on("site");

            $table->foreign("user_id")
                  ->references("id")
                  ->on("users");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop("postback");
    }
}
