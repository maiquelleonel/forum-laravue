<?php

use App\Entities\ConfigCommissionRuleOrigin as RuleOrigin;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class ConfigCommissionRuleOrigin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("config_commission_rule_origin", function(Blueprint $table){
            $table->increments("id");
            $table->string("value")->nullable();
            $table->string("name");
            $table->timestamps();
            $table->softDeletes();
        });

        RuleOrigin::create(["value"=>null,          "name"=>"Site"]);
        RuleOrigin::create(["value"=>"promoexit",   "name"=>"Promo"]);
        RuleOrigin::create(["value"=>"system",      "name"=>"Call Center"]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop("config_commission_rule_origin");
    }
}
