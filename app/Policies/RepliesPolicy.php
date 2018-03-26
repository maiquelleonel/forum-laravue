<?php

namespace App\Policies;

use App\User;
use App\Reply;
use Illuminate\Auth\Access\HandlesAuthorization;

class RepliesPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function highlight(User $user, Reply $reply)
    {
        return $user->id === $reply->thread->user_id or $user->role === 'admin';
    }
}
