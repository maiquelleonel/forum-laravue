<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddDiscountColumnToBundles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("bundles", function(Blueprint $table){
            $table->decimal("retry_discount_1")->default(0);
            $table->decimal("retry_discount_2")->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("bundles", function(Blueprint $table){
            $table->dropColumn("retry_discount_1");
            $table->dropColumn("retry_discount_2");
        });
    }
}
