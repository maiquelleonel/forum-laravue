<?php

use App\Entities\Company;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddCompanyIdToProduct extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->integer("company_id")
                  ->after("ipi")
                  ->nullable();
        });

        if($company = Company::first()){
            \App\Entities\Product::whereNull("company_id")->update([
                "company_id" => $company->id
            ]);
        }

        Schema::table('products', function (Blueprint $table) {
            $table->foreign("company_id")
                ->references("id")
                ->on("company");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign("products_company_id_foreign");
            $table->dropColumn("company_id");
        });
    }
}
