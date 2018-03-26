<?php

namespace App\Policies;

use App\User;
use App\Thread;
use Illuminate\Auth\Access\HandlesAuthorization;

class ThreadsPolicy
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

    public function update(User $user, Thread $thread)
    {
        return $user->id === $thread->user_id || $this->isAdmin($user);
    }

    public function pin(User $user)
    {
        return $this->isAdmin($user);
    }

    public function close(User $user)
    {
        return $this->isAdmin($user);
    }

    private function isAdmin($user)
    {
        return $user->role === 'admin';
    }
}
