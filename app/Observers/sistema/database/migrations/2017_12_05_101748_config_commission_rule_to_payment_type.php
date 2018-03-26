<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class ConfigCommissionRuleToPaymentType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("config_commission_rule_to_payment_type", function(Blueprint $table){
            $table->increments("id");
            $table->integer("config_commission_rule_id")->unsigned();
            $table->integer("config_commission_rule_payment_type_id")->unsigned();
        });

        Schema::table("config_commission_rule_to_payment_type", function(Blueprint $table){
            $table->foreign("config_commission_rule_id", "config_commission_rule_fk")
                  ->references("id")
                  ->on("config_commission_rule");

            $table->foreign("config_commission_rule_payment_type_id", "config_commission_rule_payment_type_fk")
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
        Schema::drop("config_commission_rule_to_payment_type");
    }
}
