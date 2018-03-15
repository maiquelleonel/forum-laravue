<?php

use App\Reply;
use App\Thread;
use Illuminate\Database\Seeder;

class RepliesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $number = 50;
        $random      = rand(5, 10);
        if (env('APP_ENV') == 'testing') {
            $number = 5;
            $random = rand(1, 5);
        }
        $threads = factory(Thread::class, $number)->create();
        $threads->each(function ($thread) use ($random) {
            factory(Reply::class, $random)->create(['thread_id' => $thread->id]);
        });
    }
}
