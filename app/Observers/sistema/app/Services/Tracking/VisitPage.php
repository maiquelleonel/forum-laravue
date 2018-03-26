<?php
/**
 * Created by PhpStorm.
 * User: dlima
 * Date: 9/26/17
 * Time: 17:16
 */

namespace App\Services\Tracking;


use App\Entities\PageVisit;
use App\Entities\PageVisitUrl;
use Carbon\Carbon;
use Illuminate\Http\Request;

class VisitPage
{
    /**
     * @var Request
     */
    private $request;

    private $visit = null;

    /**
     * VisitPage constructor.
     * @param Request $request
     * @param VariableParser $parser
     */
    public function __construct(Request $request, VariableParser $parser)
    {
        $this->request  = $request;
        $this->parser   = $parser;
        $this->visit    = $this->getCurrentVisit();
        $this->visit->touch();
    }

    /**
     * MD5 from IP + UserAgent
     * @return string
     */
    public function getVisitorId()
    {
        return md5($this->request->ip() . $this->request->server("HTTP_USER_AGENT"));
    }

    /**
     * @return PageVisit
     */
    public function getCurrentVisit()
    {
        if ($this->visit) {
            return $this->visit;
        }

        $visit = $this->findVisit();

        if (app("customer_id") && is_null($visit->customer_id)) {
            $visit->customer_id = app("customer_id");
            $visit->save();
        }

        if (app("customer_id") && $visit->customer_id != app("customer_id")) {
            return $this->makeNewVisit($visit, app("customer_id"));
        }

        $this->visit = $visit;

        return $visit;
    }

    public function trackUrl($visit)
    {
        $visit = $visit ?: $this->visit;
        $this->makeUrlVisit($visit);
    }

    private function findVisit()
    {
        $visit = null;

        // Checa por cookie
        if( $visitId = $this->getSessionCookie() ) {
            if ($visit = PageVisit::find($visitId)) {
                return $visit;
            }
        }

        // Checa pelo cookie eterno e se não está acessando com uma Campanha Nova e nem é acesso de afiliado
        if (($visitId = $this->getEternalCookie()) && !$this->parser->getCampaign() && !$this->request->has('a')) {
            if($visit = PageVisit::find($visitId)){
                // Passou o prazo de validade da visita
                if( $visit->updated_at->addSeconds(config("tracking.cookie_timelife"))->lt( Carbon::now() ) ) {
                    return $this->makeNewVisit($visit, $visit->customer_id);
                }
                // Visita dentro do prazo de validade do cookie
                $this->makeCookie($visit);
                return $visit;
            }
        }

        // Checa por UA + IP
        if ($visitorId = $this->getVisitorId()) {
            $visit = PageVisit::where("updated_at", ">=", Carbon::now()->subSeconds(config("tracking.cookie_timelife")))
                                    ->where("visitor_id", $visitorId)
                                    ->orderBy("created_at", "desc")
                                    ->first();

            if( $visit ){
                $this->makeCookie($visit);
                return $visit;
            }
        }

        // Caso não existam sessoes anteriores cria uma nova
        $visit = $this->makeVisit();
        $this->makeCookie($visit);
        return $visit;
    }

    private function makeVisit()
    {
        $data = array_merge([
            "visitor_id"    => $this->getVisitorId(),
            "customer_id"   => app("customer_id"),
            "utm_source"    => $this->parser->getSource(),
            "utm_medium"    => $this->parser->getMedium(),
            "utm_campaign"  => $this->parser->getCampaign(),
            "utm_term"      => $this->parser->getTerm(),
            "utm_content"   => $this->parser->getContent(),
            "click_id"      => $this->parser->getClickId(),
            "referrer"      => $this->request->headers->get("referer")
        ], $this->parser->getCustomVars() );

        return PageVisit::create($data);
    }

    private function makeNewVisit($visit, $customer_id)
    {
        $data = $visit->getAttributes();
        $data["customer_id"] = $customer_id;
        $data["visitor_id"]  = $this->getVisitorId();
        $data["referrer"]    = $this->request->headers->get("referer");

        $newVisit = PageVisit::create( $data );
        $this->makeCookie($newVisit);
        return $newVisit;
    }

    private function makeCookie($visit)
    {
        $cookieValue = encrypt($visit->id);
        setcookie(config("tracking.session_cookie_name"), $cookieValue, time()+config("tracking.cookie_timelife"), "/");
        setcookie(config("tracking.eternal_cookie_name"), $cookieValue, time()+config("tracking.eternal_cookie_timelife"), "/");
    }

    private function getSessionCookie()
    {
        try {
            return decrypt($_COOKIE[config("tracking.session_cookie_name")]);
        } catch (\Exception $e) {}
        return null;
    }

    private function getEternalCookie()
    {
        try {
            return decrypt($_COOKIE[config("tracking.eternal_cookie_name")]);
        } catch (\Exception $e) {}
        return null;
    }

    private function makeUrlVisit($visit)
    {
        return PageVisitUrl::create([
            "full_url"  => $this->parser->getFullUrl(),
            "prefix"    => $this->parser->getScheme(),
            "domain"    => $this->parser->getDomain(),
            "path"      => $this->parser->getPath(),
            "query"     => $this->parser->getQueryString(),
            "page_visit_id" => $visit->id
        ]);
    }
}