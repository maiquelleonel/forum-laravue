<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddSoftDeleteToOrderitems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_item_bundle', function(Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('order_item_product', function(Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_item_bundle', function(Blueprint $table) {
            $table->dropColumn("deleted_at");
        });
        Schema::table('order_item_product', function(Blueprint $table) {
            $table->dropColumn("deleted_at");
        });
    }
}
