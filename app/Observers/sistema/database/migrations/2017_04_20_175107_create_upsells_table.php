<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUpsellsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('upsells', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('from_bundle_id')->unsigned()->index('upsells_from_bundle_id_foreign');
			$table->integer('to_bundle_id')->unsigned()->index('upsells_to_bundle_id_foreign');
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
		Schema::drop('upsells');
	}

}
