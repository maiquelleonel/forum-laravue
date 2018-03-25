<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ReplyRequest;
use App\Events\NewReply;
use App\Reply;
use App\Thread;

class RepliesController extends Controller
{
    public function show($id)
    {
        $replies = Reply::with('user')->where('thread_id', $id)->get();
        return response()->json($replies->toArray());
    }

    public function store(ReplyRequest $request, Thread $thread)
    {
        $reply            = new Reply;
        $reply->body      = $request->input('body');
        $reply->user_id   = \Auth::user()->id;
        $reply->thread_id = $thread->id;
        $reply->save();

        broadcast(new NewReply($reply));

        return response()->json($reply, 201);
    }
}
