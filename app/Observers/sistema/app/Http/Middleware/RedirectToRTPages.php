<?php

namespace App\Http\Middleware;

use App\Support\SiteSettings;
use Cookie;
use Closure;

class RedirectToRTPages
{

    private $site;

    public function __construct( SiteSettings $settings){

        $this->site = $settings->getSite();
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $cookie = Cookie::get('access_without_redirect');

        $offer    = strtolower($request->offer);
        $params   = '';
        $full_url = $request->fullUrl();

        if( strpos($full_url,'?')  !== false ){
            $furl   =  explode('?', $full_url);
            $params = '?'. end( $furl );
        }

        if( is_null($cookie) && $offer != 'cake' ) {
            if( $this->site->remarketing_domain && $this->site->domain_must_redirect_to_rt ) {
                $rt_domain = $this->site->remarketing_domain;
                if(!(strpos($rt_domain,'http://') !== false)){
                    $rt_domain = 'http://' . $rt_domain;
                }
                return redirect( $rt_domain . $params);
            }
        }

        $cookie = Cookie::make("access_without_redirect", true, 1440);

        return $next($request)->cookie($cookie);
    }
}
