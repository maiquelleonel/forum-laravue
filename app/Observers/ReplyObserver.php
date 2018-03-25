<?php

namespace App\Observers;

use App\Reply;
use App\Thread;

class ReplyObserver
{
    public function created(Reply $reply)
    {
        $thread = Thread::with('replies')->find($reply->thread_id);
        $thread->total_replies = $thread->replies->count();
        $thread->save();
    }
}
