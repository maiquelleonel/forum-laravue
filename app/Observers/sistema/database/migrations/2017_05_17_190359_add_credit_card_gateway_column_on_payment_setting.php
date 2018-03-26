<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCreditCardGatewayColumnOnPaymentSetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_setting', function (Blueprint $table) {
            $table->string('creditcard_gateway')->after('pagseguro_prefix')->nullable();
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
            $table->dropColumn("creditcard_gateway");
        });
    }
}
