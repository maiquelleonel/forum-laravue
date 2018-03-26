<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddShavedStatusToCommission extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //\DB::statement('ALTER TABLE sales_commission CHANGE COLUMN status status ENUM("PENDING", "APPROVED", "PAID", "SHAVED") NULL DEFAULT NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //\DB::statement('ALTER TABLE sales_commission CHANGE COLUMN status status ENUM("PENDING", "APPROVED", "PAID") NULL DEFAULT NULL');
    }
}
