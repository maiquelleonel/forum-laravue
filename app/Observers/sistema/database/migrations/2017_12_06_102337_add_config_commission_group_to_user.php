<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddConfigCommissionGroupToUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("users", function(Blueprint $table){
            $table->integer("config_commission_group_id")->unsigned()->nullable();

            $table->foreign("config_commission_group_id")
                  ->references("id")
                  ->on("config_commission_group");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("users", function(Blueprint $table){
            $table->dropForeign(["config_commission_group_id"]);
            $table->dropColumn("config_commission_group_id");
        });
    }
}
