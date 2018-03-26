<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDiscountIpiColumnToErpSetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('erp_setting', function (Blueprint $table) {
            $table->boolean("discount_ipi_in_apps")->default(false)->after('service');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('erp_setting', function (Blueprint $table) {
            $table->dropColumn('discount_ipi_in_apps');
        });
    }
}
