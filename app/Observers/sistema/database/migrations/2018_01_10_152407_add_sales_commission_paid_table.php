<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddSalesCommissionPaidTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("sales_commission_paid", function(Blueprint $table){
            $table->increments("id");
            $table->unsignedInteger("user_id");
            $table->string("payment_receipt")->nullable();
            $table->timestamps();

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
        Schema::drop("sales_commission_paid");
    }
}
