<?php
/**
 * Created by PhpStorm.
 * User: dlima
 * Date: 11/10/17
 * Time: 09:33
 */
namespace App\Services\ExternalService;

use App\Entities\ExternalServiceSettings;
use App\Services\ExternalService\Contracts\ExternalService;
use GuzzleHttp\Client;

class Slack implements ExternalService
{
    /**
     * @var ExternalServiceSettings
     */
    private $externalServiceSettings;

    /**
     * ExternalService constructor.
     * @param ExternalServiceSettings $externalServiceSettings
     */
    public function __construct(ExternalServiceSettings $externalServiceSettings)
    {
        $this->externalServiceSettings = $externalServiceSettings;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->externalServiceSettings->service;
    }

    /**
     * @param null $data
     * @return mixed
     */
    public function sendData($data = null)
    {
        return $this->makeRequest($data);
    }

    /**
     * @param $content
     * @return \Psr\Http\Message\StreamInterface
     */
    private function makeRequest($content)
    {
        $http = new Client();
        $response = $http->request("POST", $this->externalServiceSettings->base_url, [
            'json' => $content
        ]);
        return $response->getBody();
    }
}
