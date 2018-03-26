<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddAffiliatePixelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("affiliate_pixel", function(Blueprint $table){
            $table->increments("id");
            $table->string("name");
            $table->text("code");
            $table->unsignedInteger("user_id");
            $table->integer("site_id");
            $table->string("page");
            $table->timestamps();

            $table->foreign("user_id")
                  ->references("id")
                  ->on("users");

            $table->foreign("site_id")
                  ->references("id")
                  ->on("site");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop("affiliate_pixel");
    }
}
