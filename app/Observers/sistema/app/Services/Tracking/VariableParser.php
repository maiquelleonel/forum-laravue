<?php
/**
 * Created by PhpStorm.
 * User: dlima
 * Date: 9/26/17
 * Time: 21:24
 */

namespace App\Services\Tracking;


class VariableParser
{
    protected $queryVariables = [];

    protected $domain = null;

    protected $scheme = null;

    protected $path = null;

    private $fullUrl;

    public function __construct($fullUrl)
    {
        $this->fullUrl;

        $this->urlParsed = parse_url($fullUrl);

        if (isset($this->urlParsed["query"])) {
            parse_str($this->urlParsed["query"], $this->queryVariables);
        }

        if (isset($this->urlParsed["host"])) {
            $this->domain = $this->urlParsed["host"];
        }

        if (isset($this->urlParsed["scheme"])) {
            $this->scheme = $this->urlParsed["scheme"];
        }

        if (isset($this->urlParsed["path"])) {
            $this->path = $this->urlParsed["path"];
        }

        $this->fullUrl = $fullUrl;
    }

    /**
     * @return mixed
     */
    public function getFullUrl()
    {
        return $this->fullUrl;
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->getFirstFromQuery(config("tracking.source_vars"));
    }

    /**
     * @return mixed
     */
    public function getCampaign()
    {
        return $this->getFirstFromQuery(config("tracking.campaign_vars"));
    }

    /**
     * @return mixed
     */
    public function getMedium()
    {
        return $this->getFirstFromQuery(config("tracking.medium_vars"));
    }

    /**
     * @return mixed
     */
    public function getTerm()
    {
        return $this->getFirstFromQuery(config("tracking.term_vars"));
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->getFirstFromQuery(config("tracking.content_vars"));
    }

    /**
     * @return mixed
     */
    public function getClickId()
    {
        return $this->getFirstFromQuery(config("tracking.click_vars"));
    }

    /**
     * @return mixed
     */
    public function getCustomVars()
    {
        $customVars = [];

        foreach(config("tracking.custom_vars") as $index => $varName) {
            if ($varName) {
                $customVars["custom_var_k{$index}"] = $varName;
                $customVars["custom_var_v{$index}"] = $this->getFirstFromQuery([$varName]);
            }
        }

        return $customVars;
    }

    /**
     * @param $varNames
     * @return mixed
     */
    private function getFirstFromQuery($varNames)
    {
        foreach($varNames as $varName){
            if (isset($this->queryVariables[$varName]) && $this->queryVariables[$varName]) {
                return $this->queryVariables[$varName];
            }
        }

        return null;
    }

    public function getDomain()
    {
        return $this->domain;
    }

    public function getScheme()
    {
        return $this->scheme;
    }

    public function getQueryString()
    {
        return isset($this->urlParsed["query"]) ? $this->urlParsed["query"] : null;
    }

    public function getPath()
    {
        return $this->path;
    }
}