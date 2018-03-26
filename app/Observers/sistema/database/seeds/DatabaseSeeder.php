<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call(PermissionsTableSeeder::class);
        $this->call(ExternalSettingsPermissionSeeder::class);
        $this->call(RolesTableSeeder::class);
        $this->call(UserTableSeeder::class);
        $this->call(SiteTableSeeder::class);
        $this->call(ProductTableSeeder::class);
        $this->call(BundleTableSeeder::class);
        $this->call(ExternalServiceSettingsSeeder::class);
        $this->call(EvoluxLoginReportSeeder::class);

        Model::reguard();
    }
}
