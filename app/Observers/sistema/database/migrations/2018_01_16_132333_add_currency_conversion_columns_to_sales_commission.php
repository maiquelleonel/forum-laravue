<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddCurrencyConversionColumnsToSalesCommission extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("sales_commission", function(Blueprint $table){
            $table->unsignedInteger("currency_id")->default(1)->after("paid_at");
            $table->decimal("conversion_rate")->default(1)->after("currency_id");
            $table->foreign("currency_id")
                  ->references("id")
                  ->on("currencies");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("sales_commission", function(Blueprint $table){
            $table->dropForeign("sales_commission_currency_id_foreign");
            $table->dropColumn("currency_id");
            $table->dropColumn("conversion_rate");
        });
    }
}
