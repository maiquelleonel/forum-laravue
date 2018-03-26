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
        $thread = Thread::with('replies.user')->find($id);
        return response()->json($thread->replies->toArray());
    }

    public function store(ReplyRequest $request, Thread $thread)
    {
        if ($thread->isOpen) {
            $reply            = new Reply;
            $reply->body      = $request->input('body');
            $reply->user_id   = \Auth::user()->id;
            $reply->thread_id = $thread->id;
            $reply->save();

            broadcast(new NewReply($reply));

            return response()->json($reply, 201);
        } else {
            return response()->json(['error' => 'This thread is closed!'], 403);
        }
    }

    public function highlighter(Reply $reply)
    {
        $this->authorize('highlight', $reply);

        Reply::where([
            ['thread_id', '=', $reply->thread_id],
            ['id', '<>', $reply->id],
        ])
        ->update([
            'highlighted' => false,
        ]);

        $reply->highlighted = true;
        $reply->save();

        return back();
    }
}
