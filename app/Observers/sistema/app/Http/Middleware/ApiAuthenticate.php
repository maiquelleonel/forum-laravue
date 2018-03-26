<?php

namespace App\Http\Middleware;

use App\Entities\ApiKey;
use Artesaos\Defender\Exceptions\ForbiddenException;
use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Auth;

class ApiAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     * @throws ForbiddenException
     */
    public function handle($request, Closure $next)
    {
        if($token = $request->header("access-token", $request->get("access-token"))){
            if($api = ApiKey::where("access_token", $token)->first()){
                auth()->login($api->user);

                session()->set("site_id", $api->site_id);
                session()->set("user_id", $api->user_id);

                $response = $next($request);

                session()->flush();
                auth()->logout();

                return $response;
            }
        }
        throw new ForbiddenException("Invalid Access Token");
    }
}
