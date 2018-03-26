<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class ConfigCommissionRuleSiteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("config_commission_rule_site", function(Blueprint $table){
            $table->increments("id");
            $table->integer("config_commission_rule_id")->unsigned();
            $table->integer("site_id");
        });

        Schema::table("config_commission_rule_site", function(Blueprint $table){
            $table->foreign("config_commission_rule_id")
                ->references("id")
                ->on("config_commission_rule");

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
        Schema::drop("config_commission_rule_site");
    }
}
