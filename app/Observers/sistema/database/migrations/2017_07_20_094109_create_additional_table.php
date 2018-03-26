<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateAdditionalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("additional", function(Blueprint $table){
            $table->increments("id");
            $table->integer("from_bundle_id")->unsigned()->index();
            $table->integer("product_id")->unsigned()->index();
            $table->integer("order")->default(1);
            $table->integer("qty_max");
            $table->decimal("price");
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
        Schema::drop("additional");
    }
}
