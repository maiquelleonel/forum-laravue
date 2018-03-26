<?php

namespace App\Http\Middleware;

use Cache;
use Closure;
use Illuminate\Http\Request;

class CloudFlareProxies
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {

        $proxy_ips = Cache::remember('cloudFlareProxyIps', 1440, function () {
            $url = 'https://www.cloudflare.com/ips-v4';
            $ips = file_get_contents($url);
            return array_filter(explode("\n", $ips));
        });

        $request->setTrustedProxies($proxy_ips);

        return $next($request);
    }
}