<?php

namespace App\Http\Middleware;

use Closure;

class ChangeUserLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($user = auth()->user()) {
            if ($user->locale) {
                app()->setLocale($user->locale);
            }
        }

        return $next($request);
    }
}
