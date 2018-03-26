<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPromocialPixelsToPixelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pixels', function (Blueprint $table) {
            $table->text("page_promoexit")->after("page_success_pagseguro")->nullable();
            $table->text("page_retargeting")->after("page_success_pagseguro")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pixels', function (Blueprint $table) {
            $table->dropColumn("page_promoexit");
            $table->dropColumn("page_retargeting");
        });
    }
}
