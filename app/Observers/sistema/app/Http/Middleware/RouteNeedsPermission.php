<?php

namespace App\Http\Middleware;

use App\Exceptions\Handlers\ForbiddenHandler;
use Closure;

class RouteNeedsPermission
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
        $actions = $request->route()->getAction();

        $routeName = array_get($actions, "as");

        if ($routeName && !auth()->user()->hasPermission( $routeName )) {
            return $this->forbiddenResponse();
        }

        return $next($request);
    }

    /**
     * Handles the forbidden response.
     *
     * @return mixed
     */
    protected function forbiddenResponse()
    {
        $handler = app()->make(config('defender.forbidden_callback'));

        return ($handler instanceof ForbiddenHandler) ? $handler->handle() : response('Forbidden', 403);
    }
}