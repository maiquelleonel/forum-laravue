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
        $res = $this->actingAs($user)->json('GET', route('threads.index'));
        $res->assertStatus(200)
            ->assertJsonFragment([ $threads['data'] ]);
    }

    public function testStoreAction()
    {
        $user = factory(User::class)->create();
        $data = [
            'title' => 'Meu primeiro tópico',
            'body'  => 'Teste! Testando! 1, 2, 3!'
        ];

        $res = $this->actingAs($user)
                    ->json('POST', route('thread.store'), $data);

        $thread = Thread::first();

        $res->assertStatus(201)
            ->assertJsonFragment(['created' => 'success'])
            ->assertJsonFragment(['body' => $thread->body ]);
    }

    public function testUpdateAction()
    {
        $user = factory(User::class)->create();
        $thread = factory(Thread::class)->create();
        $update_data = [
            'title' => 'Meu titulo atualizado',
            'body'  => 'Meu conteudo atualizado',
        ];

        $res = $this->actingAs($user)
                    ->json('PUT', route('thread.update', $thread), $update_data);

        $res->assertStatus(302);
        $this->assertEquals($update_data, Thread::first()->only('title', 'body'));
    }

    public function testEditWithInvalidData()
    {
        $user = factory(User::class)->create();
        $thread = factory(Thread::class)->create();
        $update_data = [
            'title' => 'Meu',
            'body'  => 'Meu conteudo atualizado',
        ];
        $res = $this->actingAs($user)
                    ->json('PUT', route('thread.update', $thread), $update_data);
        //$res->assertStatus(302);
        $this->assertNotEquals(Thread::first()->only('title'), $update_data['title']);
    }
}
