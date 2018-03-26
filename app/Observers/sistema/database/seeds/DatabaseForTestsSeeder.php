<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseForTestsSeeder extends Seeder
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
        $this->call(RolesTableSeeder::class);
        $this->call(UserTableSeeder::class);
        $this->call(SiteTestTableSeeder::class);
        $this->call(ProductTableSeeder::class);
        $this->call(BundleTestsTableSeeder::class);
        $this->call(ExternalSettingsPermissionSeeder::class);
        $this->call(ExternalServiceSettingsSeeder::class);
        
        Model::reguard();
    }
}
