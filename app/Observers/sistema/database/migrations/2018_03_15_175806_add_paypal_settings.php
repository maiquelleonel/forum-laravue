<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPaypalSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_setting', function (Blueprint $table) {
            $table->string("paypal_environment")->nullable();
            $table->string("paypal_client_id")->nullable();
            $table->string("paypal_secret_key")->nullable();
            $table->string("paypal_description")->nullable();
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
            $table->dropColumn("paypal_environment");
            $table->dropColumn("paypal_client_id");
            $table->dropColumn("paypal_secret_key");
            $table->dropColumn("paypal_description");
        });
    }
}
