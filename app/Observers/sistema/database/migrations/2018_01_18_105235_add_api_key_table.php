<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddApiKeyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("api_key", function(Blueprint $table){
            $table->increments("id");
            $table->string("access_token")->unique();
            $table->unsignedInteger("user_id");
            $table->integer("site_id");
            $table->timestamps();

            $table->index("access_token");
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
        Schema::drop("api_key");
    }
}
