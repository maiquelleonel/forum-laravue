<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameSiteIdColumnAtBundles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("bundles", function(Blueprint $table){
            $table->renameColumn("site_id", "bundle_group_id");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("bundles", function(Blueprint $table){
            $table->renameColumn('bundle_group_id', 'site_id');
        });
    }
}
