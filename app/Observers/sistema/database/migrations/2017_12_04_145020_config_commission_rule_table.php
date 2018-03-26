<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ConfigCommissionRuleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("config_commission_rule", function(Blueprint $table){
            $table->increments("id");
            $table->integer("config_commission_group_id")->unsigned();
            $table->enum("type", ["PERCENTAGE", "FIXED"]);
            $table->decimal("value");
            $table->timestamps();
        });

        Schema::table("config_commission_rule", function(Blueprint $table){
            $table->foreign("config_commission_group_id")
                  ->references("id")
                  ->on("config_commission_group");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop("config_commission_rule");
    }
}
