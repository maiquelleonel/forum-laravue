<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBundlesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('bundles', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('site_id')->index('site_id');
			$table->text('image', 65535)->nullable();
			$table->string('name');
			$table->text('description', 65535)->nullable();
			$table->string('category')->default('default');
			$table->integer('installments')->default(2);
			$table->decimal('freight_value', 10)->default(0.00);
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
		Schema::drop('bundles');
	}

}
