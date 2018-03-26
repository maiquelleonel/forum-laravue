<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBoletoFacilColumnsToPayment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_setting', function (Blueprint $table) {
            $table->string("boleto_facil_apikey")->nullable()->after('asaas_days_expiration');
            $table->string("boleto_facil_environment")->nullable()->after('asaas_days_expiration');
            $table->string("boleto_facil_description")->nullable()->after('asaas_days_expiration');
            $table->string("boleto_facil_days_expiration")->nullable()->after('asaas_days_expiration');
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
            $table->dropColumn("boleto_facil_apikey");
            $table->dropColumn("boleto_facil_environment");
            $table->dropColumn("boleto_facil_description");
            $table->dropColumn("boleto_facil_days_expiration");
        });
    }
}
