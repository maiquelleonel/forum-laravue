<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddCurrencyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("currencies", function(Blueprint $table){
            $table->increments("id");
            $table->string("name");
            $table->string("code", 3);
            $table->string("prefix")->nullable();
            $table->string("suffix")->nullable();
            $table->integer("decimals")->default(0);
            $table->string("decimal")->nullable();
            $table->string("thousand")->nullable();
            $table->decimal("conversion_rate", 8, 4)->default(1);
            $table->timestamps();
        });

        $currencies = [
            [
                "name"      => "Real",
                "code"      => "BRL",
                "prefix"    => "R$ ",
                "decimal"   => ",",
                "decimals"  => "2",
                "thousand"  => ".",
                "suffix"    => "",
                "conversion_rate"=>1
            ],
            [
                "name"      => "Dollar",
                "code"      => "USD",
                "prefix"    => "$ ",
                "decimal"   => ".",
                "decimals"  => "2",
                "thousand"  => ",",
                "suffix"    => "",
                "conversion_rate"=>0.31117
            ]
        ];

        foreach($currencies as $currency){
            \App\Entities\Currency::create( $currency );
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop("currencies");
    }
}
