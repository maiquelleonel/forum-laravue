<?php
use App\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $num = env('APP_ENV') == 'testing' ? 5 : 50;
        factory(User::class, $num)->create();
    }
}
