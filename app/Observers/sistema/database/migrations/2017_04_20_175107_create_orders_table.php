<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('orders', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('customer_id')->unsigned()->index();
			$table->decimal('total', 12);
			$table->string('status', 100)->default('cancelado');
			$table->decimal('freight_value', 12)->default(0.00);
			$table->string('freight_type', 100)->default('PAC');
			$table->string('tracking');
			$table->string('payment_type', 100)->nullable();
			$table->string('payment_type_collection', 100)->nullable();
			$table->integer('installments')->default(1);
			$table->string('origin')->nullable();
			$table->integer('user_id')->nullable()->index('user_id');
			$table->dateTime('paid_at')->nullable();
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
		Schema::drop('orders');
	}

}
