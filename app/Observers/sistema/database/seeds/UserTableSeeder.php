<?php

use App\Entities\User;
use Artesaos\Defender\Role;
use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::create([
            "name"      => "Administrador",
            "email"     => "admin",
            "password"  => bcrypt("admin")
        ]);

        $roles = Role::lists("id");

        $user->syncRoles( $roles->toArray() );
    }
}
