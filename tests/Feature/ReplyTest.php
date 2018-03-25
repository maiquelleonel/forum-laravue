<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\User;
use App\Reply;
use App\Thread;

class ReplyTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testRepliesListByThread()
    {
        $this->seed('DatabaseSeeder');
        $user    = factory(User::class)->create();
        $replies = Reply::where('thread_id', 1)->get();
        $route   = route('replies.index', $replies->first()->thread_id);
        $res     = $this->actingAs($user)->json('GET', $route);
        $res->assertStatus(200);
        $res->assertJsonFragment(['body' => $replies->first()->body ]);
    }

    public function testCreateReply()
    {
        $user   = factory(User::class)->create();
        $thread = factory(Thread::class)->create();
        $data   = [
            'body'      => 'Minha primeira resposta',
            'user_id'   => $user->id,
            'thread_id' => $thread->id,
        ];
        $route = route('reply.store', $thread);
        $res   = $this->actingAs($user)->json('POST', $route, $data);
        $reply = Reply::where('thread_id', $thread->id)->first();
        $res->assertStatus(201)
            ->assertJsonFragment([ 'body' => $reply->body ]);
    }
}
