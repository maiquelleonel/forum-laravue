<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class ConfigCommissionRuleToOrigin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("config_commission_rule_to_origin", function(Blueprint $table){
            $table->increments("id");
            $table->integer("config_commission_rule_id")->unsigned();
            $table->integer("config_commission_rule_origin_id")->unsigned();
        });

        Schema::table("config_commission_rule_to_origin", function(Blueprint $table){
            $table->foreign("config_commission_rule_id", "config_commission_rule_origin_fk")
                ->references("id")
                ->on("config_commission_rule");

            $table->foreign("config_commission_rule_origin_id", "config_commission_origin_fk")
                ->references("id")
                ->on("config_commission_rule_origin");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop("config_commission_rule_to_origin");
    }
}
