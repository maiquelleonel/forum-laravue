<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('products', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('sku', 20)->nullable();
			$table->decimal('price', 12);
			$table->string('name');
			$table->text('description', 65535)->nullable();
			$table->text('image', 65535)->nullable();
			$table->integer('inventory');
			$table->decimal('cost', 12)->nullable();
			$table->decimal('ipi', 10, 0)->default(0);
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
		Schema::drop('products');
	}

}
