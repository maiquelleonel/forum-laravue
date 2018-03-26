<?php
/**
 * Created by PhpStorm.
 * User: dlima
 * Date: 12/22/17
 * Time: 11:13
 */

namespace App\Services\PostBack;


use App\Entities\PageVisit;
use App\Entities\PostBack as PostBackEntity;
use App\Services\Tracking\OutputVariableParser;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

class PostBack
{
    /**
     * @var ClientInterface
     */
    protected $httpClient;
    /**
     * @var OutputVariableParser
     */
    private $variableParser;

    /**
     * PostBack constructor.
     * @param OutputVariableParser $variableParser
     */
    public function __construct(OutputVariableParser $variableParser)
    {
        $this->variableParser = $variableParser;
    }

    /**
     * @param PostBackEntity $postBack
     * @param PageVisit $visit
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function sendPostBack(PostBackEntity $postBack, PageVisit $visit)
    {
        $vars = $this->variableParser->getVars($visit);
        $url  = $this->variableParser->parseString($postBack->url, $vars);
        return $this->sendRequest($url, $postBack->method);
    }

    /**
     * @param ClientInterface $client
     */
    public function setHttpClient(ClientInterface $client)
    {
        $this->httpClient = $client;
    }

    /**
     * @return ClientInterface
     */
    public function getHttpClient()
    {
        if(!$this->httpClient){
            $this->httpClient = app(Client::class);
        }

        return $this->httpClient;
    }

    /**
     * @param string $url
     * @param string $method
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function sendRequest($url, $method = "POST")
    {
        $client = $this->getHttpClient();
        return $client->request($method, $url);
    }
}