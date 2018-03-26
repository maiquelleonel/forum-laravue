<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHashFieldOnCustomer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("customers", function (Blueprint $table) {
            $table->string('hash')->nullable()->after('telephone')->index('customer_hash');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("customers", function (Blueprint $table) {
            $table->dropIndex('customer_hash');
            $table->dropColumn('hash');
        });
    }
}
