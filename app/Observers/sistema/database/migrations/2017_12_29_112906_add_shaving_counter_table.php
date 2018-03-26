<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddShavingCounterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("shaving_counter", function(Blueprint $table){
            $table->increments("id");
            $table->integer("site_id");
            $table->unsignedInteger("user_id");
            $table->unsignedInteger("config_commission_rule_origin_id");
            $table->unsignedInteger("config_commission_rule_payment_type_id");
            $table->integer("orders_qty")->default(0);
            $table->decimal("orders_amount")->default(0);
            $table->integer("commissions_paid_qty")->default(0);
            $table->decimal("commissions_paid_amount")->default(0);
            $table->timestamps();

            $table->foreign("site_id")
                  ->references("id")
                  ->on("site");

            $table->foreign("user_id")
                  ->references("id")
                  ->on("users");

            $table->foreign("config_commission_rule_origin_id", "shaving_config_commission_origin_fk")
                  ->references("id")
                  ->on("config_commission_rule_origin");

            $table->foreign("config_commission_rule_payment_type_id", "shaving_config_commission_rule_payment_type_fk")
                ->references("id")
                ->on("config_commission_rule_payment_type");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop("shaving_counter");
    }
}
