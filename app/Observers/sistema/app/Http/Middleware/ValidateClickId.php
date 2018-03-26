<?php

namespace App\Http\Middleware;

use Closure;

class ValidateClickId
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
        if( $request->has('clickid') or $request->has('click_id')) {
            $click_id = $request->clickid ?: $request->click_id;
            session()->put('click_id', $click_id );
        }

        if( $request->has('source')) {
            $source = $request->source;
            session()->put('source', $source);
        }

        //dd(session('source'), session('click_id'));

        return $next($request);
    }
}
