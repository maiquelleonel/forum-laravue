<?php

namespace App\Listeners;

use App\Domain\NotificationType;
use App\Entities\OrderNotification;
use App\Entities\User;
use App\Events\OrderAuthorized;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Request;

class SendUserAccessMail implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param User $user
     */
    public function handle(User $user)
    {
        if ( $user->new_password ) {
            $message = function ($m) use ($user) {
                $m->from(config('mail.from.address'), config('mail.from.name'))
                    ->to($user->email, $user->name)
                    ->subject("Acesso ao Sistema");
            };

            \Mail::send("admin.email.user-created", compact("user"), $message);
        }
    }
}
