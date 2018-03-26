<?php

namespace App\Http\Middleware;

use App\Services\Tracking\VisitPage;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TrackPageViews
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        /**
         * @var \Illuminate\Http\Response $response
         */
        $response = $next($request);

        if( $response instanceof Response && config("tracking.enabled") && !$request->is("pixel*")) {
            return $this->trackPage($request, $response);
        }

        return $response;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return mixed
     */
    private function trackPage(Request $request, Response $response)
    {
        try {
            /**
             * @var $visitPage VisitPage
             */
            $visitPage = app(VisitPage::class);
            $visit = $visitPage->getCurrentVisit();
            $visitPage->trackUrl( $visit );

        } catch (\Exception $e) {
            \Log::error( $e );
        }

        return $response;
    }
}
