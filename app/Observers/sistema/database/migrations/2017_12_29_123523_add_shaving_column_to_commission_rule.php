<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddShavingColumnToCommissionRule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('config_commission_rule', function (Blueprint $table) {
            $table->decimal("shaving_rate")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('config_commission_rule', function (Blueprint $table) {
            $table->dropColumn("shaving_rate");
        });
    }
}
