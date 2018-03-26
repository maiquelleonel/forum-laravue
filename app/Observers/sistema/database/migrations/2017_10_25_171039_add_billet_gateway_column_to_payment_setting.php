<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBilletGatewayColumnToPaymentSetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_setting', function (Blueprint $table) {
            $table->string('billet_gateway')->default("Asaas")->after('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_setting', function (Blueprint $table) {
            $table->dropColumn('billet_gateway');
        });
    }
}
