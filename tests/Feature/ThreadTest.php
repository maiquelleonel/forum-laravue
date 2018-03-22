<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\User;
use App\Thread;

class ThreadTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * A basic test example.
     *
     * @return void
     */

    public function testIndexAction()
    {
        $this->seed('ThreadsTableSeeder');
        $user = factory(User::class)->create();
        $threads = Thread::orderBy('updated_at', 'desc')->paginate()->toArray();
        $res = $this->actingAs($user)->json('GET', '/threads');
        $res->assertStatus(200)
            ->assertJsonFragment([ $threads['data'] ]);
    }

    public function testStoreAction()
    {
        $user = factory(User::class)->create();

        $res = $this->actingAs($user)->json('POST', '/threads', [
            'title' => 'Meu primeiro tópico',
            'body'  => 'Teste! Testando! 1, 2, 3!'
        ]);
        $thread = Thread::first();
        $res->assertStatus(201)
            ->assertJsonFragment(['created' => 'success'])
            ->assertJsonFragment($thread->toArray());
    }
}
