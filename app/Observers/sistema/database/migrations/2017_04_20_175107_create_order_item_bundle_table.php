<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderItemBundleTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('order_item_bundle', function(Blueprint $table)
		{
			$table->increments('id');
			$table->decimal('price', 12);
			$table->integer('qty');
			$table->integer('order_id')->unsigned()->index();
			$table->integer('bundle_id')->unsigned()->nullable()->index('order_item_bundle_bundle_id_foreign');
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
		Schema::drop('order_item_bundle');
	}

}
