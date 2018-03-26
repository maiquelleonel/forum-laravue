<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddInvoiceValidatorColumnsToErpSetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("erp_setting", function(Blueprint $table){
            $table->boolean("generate_invoice")->default(false);
            $table->boolean("run_validations")->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("erp_setting", function(Blueprint $table){
            $table->dropColumn("generate_invoice");
            $table->dropColumn("run_validations");
        });
    }
}
