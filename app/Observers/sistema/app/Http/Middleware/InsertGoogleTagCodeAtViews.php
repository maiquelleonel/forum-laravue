<?php

namespace App\Http\Middleware;

use App\Entities\Site;
use App\Support\SiteSettings;
use Closure;
use Illuminate\Http\Response;

class InsertGoogleTagCodeAtViews
{
    /**
     * @var SiteSettings
     */
    private $siteSettings;

    /**
     * InsertGoogleTagCodeAtViews constructor.
     * @param SiteSettings $siteSettings
     */
    public function __construct(SiteSettings $siteSettings)
    {
        $this->siteSettings = $siteSettings;
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
        /**
         * @var \Illuminate\Http\Response $response
         */
        $response = $next($request);
        $site = $this->siteSettings->getSite();

        if ($this->allowParse($site, $response)) {
            $content = $response->getContent();
            $content = $this->parseTags($content, $site->gtm_code);
            $response->setContent($content);
        }

        return $response;
    }

    /**
     * @param Site $site
     * @param Response $response
     * @return bool
     */
    private function allowParse($site, $response)
    {
        return $site && $site->gtm_code && !$response->isRedirect() && $response->isOk();
    }

    /**
     * @param $content
     * @param $gtm_code
     * @return mixed
     */
    private function parseTags($content, $gtm_code)
    {
        $headTag = str_ireplace(config("google-tag.var_name"), $gtm_code, config("google-tag.header"));
        $bodyTag = str_ireplace(config("google-tag.var_name"), $gtm_code, config("google-tag.body"));

        return str_ireplace(
            ["<head>", "<body>"],
            ["<head>{$headTag}", "<body>{$bodyTag}"],
            $content);
    }
}
