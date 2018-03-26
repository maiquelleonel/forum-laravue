<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePixelsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('pixels', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('name', 100);
			$table->text('page_home', 65535);
			$table->text('page_bundles', 65535)->nullable();
			$table->text('page_preorder', 65535)->nullable();
			$table->text('page_checkout', 65535)->nullable();
			$table->text('page_checkout_retry', 65535)->nullable();
			$table->text('page_upsell', 65535)->nullable();
			$table->text('page_additional', 65535)->nullable();
			$table->text('page_success_creditcard', 65535)->nullable();
			$table->text('page_success_boleto', 65535)->nullable();
			$table->text('page_success_pagseguro', 65535)->nullable();
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
		Schema::drop('pixels');
	}

}
