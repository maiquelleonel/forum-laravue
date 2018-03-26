<?php

namespace App\Http\Middleware;

use Closure;

class CustomerCanBuy
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
        if (app('customer_id')) {
            return $next($request);
        }

        return redirect()
                ->route($request->input('redirect', 'home'))
                ->withInput()
                ->with('error', 'Dados cadastrais não identificados, preencha todos os campos e tente novamente!');
    }
}