<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypePaypalToTransactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE transactions CHANGE COLUMN type type ENUM(
          'CARTAO','BOLETO','TRANSFERENCIA','PAGSEGURO', 'PAYPAL'
        )");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE transactions CHANGE COLUMN type type ENUM(
          'CARTAO','BOLETO','TRANSFERENCIA','PAGSEGURO'
        )");
    }
}
