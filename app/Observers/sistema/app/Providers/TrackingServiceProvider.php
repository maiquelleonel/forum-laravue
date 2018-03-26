<?php

namespace App\Providers;

use App\Services\Tracking\VariableParser;
use App\Services\Tracking\VisitPage;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class TrackingServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(VariableParser::class, function(){

            $request = request();

            if ($request->is(config('tracking.iframe_route'))) {

                $vars = array_merge(config('tracking.source_vars'), config('tracking.campaign_vars'));

                if( count( array_intersect(array_keys($request->all()), $vars ) ) > 0 ){
                    $url = $request->fullUrl();
                } else {
                    $url = $request->headers->get("referer") ?: $request->server("HTTP_REFERER") ?: $request->fullUrl();
                }

                return new VariableParser( $url );
            }
            return new VariableParser( $request->fullUrl() );
        });

        $this->app->singleton(VisitPage::class, function (){
            return new VisitPage(app(Request::class), app(VariableParser::class));
        });
    }
}
