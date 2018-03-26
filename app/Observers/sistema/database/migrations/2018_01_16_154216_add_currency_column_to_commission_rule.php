<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCurrencyColumnToCommissionRule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('config_commission_rule', function (Blueprint $table) {
            $table->unsignedInteger("currency_id")->default(1)->after("shaving_rate");
            $table->foreign("currency_id")
                ->references("id")
                ->on("currencies");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('config_commission_rule', function (Blueprint $table) {
            $table->dropForeign("config_commission_rule_currency_id_foreign");
            $table->dropColumn("currency_id");
        });
    }
}
