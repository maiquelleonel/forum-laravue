<?php

namespace App\Services\ExternalService\Contracts;


use App\Entities\ExternalServiceSettings;

interface ExternalService
{
    /**
     * ExternalService constructor.
     * @param ExternalServiceSettings $externalServiceSettings
     */
    public function __construct(ExternalServiceSettings $externalServiceSettings);

    /**
     * @return mixed
     */
    public function getType();

    /**
     * @param null $data
     * @return mixed
     */
    public function sendData($data = null);
}