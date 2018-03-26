<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class SalesCommissionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("sales_commission", function(Blueprint $table){
            $table->increments("id");
            $table->integer("order_id")->unsigned();
            $table->integer("user_id")->unsigned();
            $table->decimal("value");
            $table->enum("status", ["PENDING", "APPROVED", "PAID", "SHAVED"])->default("PENDING");
            $table->dateTime("paid_at")->nullable();
            $table->timestamps();
        });

        Schema::table("sales_commission", function(Blueprint $table) {
            $table->foreign("order_id")->references("id")->on("orders");
            $table->foreign("user_id")->references("id")->on("users");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop("sales_commission");
    }
}
