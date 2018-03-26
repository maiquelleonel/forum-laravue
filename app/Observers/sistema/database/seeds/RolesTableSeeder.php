<?php

use Artesaos\Defender\Permission;
use Artesaos\Defender\Role;
use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role = Role::create([
            "name"  => "Administrador"
        ]);

        $permissions = Permission::lists("id");

        $role->syncPermissions( $permissions->toArray() );
    }
}
