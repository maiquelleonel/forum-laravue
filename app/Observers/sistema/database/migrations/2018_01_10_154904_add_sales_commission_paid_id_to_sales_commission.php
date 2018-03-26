<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSalesCommissionPaidIdToSalesCommission extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales_commission', function (Blueprint $table) {
            $table->unsignedInteger("sales_commission_paid_id")
                  ->nullable()
                  ->after("paid_at");

            $table->foreign("sales_commission_paid_id")
                  ->references("id")
                  ->on("sales_commission_paid");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales_commission', function (Blueprint $table) {
            $table->dropForeign("sales_commission_sales_commission_paid_id_foreign");
            $table->dropColumn("sales_commission_paid_id");
        });
    }
}
