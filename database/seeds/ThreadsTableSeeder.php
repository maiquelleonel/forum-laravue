<?php

use App\Thread;
use App\Reply;
use Illuminate\Database\Seeder;

class ThreadsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $num = env('APP_ENV') == 'testing' ? 5 : 50;
        factory(Thread::class, $num)->create();
    }
}
