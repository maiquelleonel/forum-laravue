<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSiteTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('site', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('name', 50);
			$table->string('domain', 200);
			$table->string('color', 20)->default('green');
			$table->string('theme', 100)->default('theme-green');
			$table->string('view_folder', 150)->nullable();
			$table->integer('payment_setting_id')->index('payment_setting_id');
			$table->integer('pixels_id')->nullable()->index('pixels_id');
			$table->integer('company_id')->nullable()->index('company_id');
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
		Schema::drop('site');
	}

}
