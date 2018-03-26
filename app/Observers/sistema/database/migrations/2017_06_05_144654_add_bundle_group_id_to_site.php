<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddBundleGroupIdToSite extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("site", function(Blueprint $table){
            $table->integer("bundle_group_id")
                  ->after('path_version')
                  ->index()
                  ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("site", function(Blueprint $table){
            $table->dropColumn("bundle_group_id");
        });
    }
}
