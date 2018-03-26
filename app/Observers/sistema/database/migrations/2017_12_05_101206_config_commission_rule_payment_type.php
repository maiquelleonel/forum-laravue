<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use \App\Entities\ConfigCommissionRulePaymentType as RulePaymntType;

class ConfigCommissionRulePaymentType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("config_commission_rule_payment_type", function(Blueprint $table){
            $table->increments("id");
            $table->string("value")->nullable();
            $table->string("name");
            $table->timestamps();
            $table->softDeletes();
        });

        RulePaymntType::create(["value"=>"CreditCard",  "name"=>"Cartão de Crédito"]);
        RulePaymntType::create(["value"=>"Boleto",      "name"=>"Boleto"]);
        RulePaymntType::create(["value"=>"Pagseguro",   "name"=>"PagSeguro"]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop("config_commission_rule_payment_type");
    }
}
