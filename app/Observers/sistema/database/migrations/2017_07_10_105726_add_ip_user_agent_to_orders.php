<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIpUserAgentToOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string("ip")->nullable()->after('click_id');
            $table->text("user_agent")->nullable('click_id');
            $table->string("device")->nullable('click_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn("ip");
            $table->dropColumn("user_agent");
            $table->dropColumn("device");
        });
    }
}
