<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToUpsellsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('upsells', function(Blueprint $table)
		{
			$table->foreign('from_bundle_id')->references('id')->on('bundles')->onUpdate('RESTRICT')->onDelete('RESTRICT');
			$table->foreign('to_bundle_id')->references('id')->on('bundles')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('upsells', function(Blueprint $table)
		{
			$table->dropForeign('upsells_from_bundle_id_foreign');
			$table->dropForeign('upsells_to_bundle_id_foreign');
		});
	}

}
