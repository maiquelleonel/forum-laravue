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
        $threads = factory( Thread::class, 50 )->create();
        $threads->each( function( $thread ){
            factory( Reply::class, rand(5, 10) )->create(['thread_id' => $thread->id ]);
        });
    }
}
