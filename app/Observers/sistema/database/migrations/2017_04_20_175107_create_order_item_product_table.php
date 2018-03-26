<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderItemProductTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('order_item_product', function(Blueprint $table)
		{
			$table->increments('id');
			$table->decimal('price', 12);
			$table->integer('qty');
			$table->integer('order_id')->unsigned()->index();
			$table->integer('product_id')->unsigned()->nullable()->index('order_item_product_product_id_foreign');
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
		Schema::drop('order_item_product');
	}

}
