<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('customers', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('firstname', 100);
			$table->string('lastname', 100);
			$table->string('email');
			$table->string('telephone', 45);
			$table->string('postcode', 45)->nullable();
			$table->string('address_street')->nullable();
			$table->string('address_street_number', 10)->nullable();
			$table->string('address_street_complement', 30)->nullable();
			$table->string('address_street_district', 45)->nullable();
			$table->string('address_city', 45)->nullable();
			$table->string('address_state', 45)->nullable();
			$table->string('document_number', 45)->nullable();
			$table->integer('site_id');
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
		Schema::drop('customers');
	}

}
