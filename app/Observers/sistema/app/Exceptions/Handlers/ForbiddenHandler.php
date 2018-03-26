<?php

namespace App\Exceptions\Handlers;

use Artesaos\Defender\Exceptions\ForbiddenException;
use Artesaos\Defender\Contracts\ForbiddenHandler as ForbiddenHandlerContract;


class ForbiddenHandler implements ForbiddenHandlerContract
{
    public function handle()
    {
        $user   = auth()->user();
        $url    = request()->fullUrl();
        $message = "{$user->name}({$user->id}) Forbidden access: {$url}";

        throw new ForbiddenException($message);
    }
}