<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentSettingTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('payment_setting', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('name', 100);
			$table->string('mundipagg_merchantkey', 150)->nullable();
			$table->string('mundipagg_environment', 100)->nullable();
			$table->string('mundipagg_payment_method', 100)->nullable();
			$table->string('mundipagg_transaction_prefix', 100)->nullable();
			$table->string('mundipagg_softdescriptor', 13)->nullable();
			$table->decimal('credit_card_interest', 10, 0)->nullable();
			$table->string('credit_card_acquirers', 200)->nullable();
			$table->string('asaas_apikey', 150)->nullable();
			$table->string('asaas_environment', 100)->nullable();
			$table->string('asaas_boleto_description', 300)->nullable();
			$table->integer('asaas_days_expiration')->nullable()->default(2);
			$table->string('pagseguro_email', 100)->nullable();
			$table->string('pagseguro_token', 150)->nullable();
			$table->string('pagseguro_environment', 100)->nullable();
			$table->string('pagseguro_prefix', 50)->nullable();
			$table->string('payments', 100)->default('creditcard');
			$table->string('retry_payments', 100)->default('creditcard,boleto,pagseguro');
			$table->timestamps();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('payment_setting');
	}

}
