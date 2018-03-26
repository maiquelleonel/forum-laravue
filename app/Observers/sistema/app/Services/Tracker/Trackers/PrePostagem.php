<?php

namespace App\Services\Tracker\Trackers;


use Cagartner\CorreiosConsulta\CorreiosConsulta;
use Carbon\Carbon;
use App\Services\Tracker\TrackerContract;
use App\Services\Tracker\TrackerDetails;
use App\Services\Tracker\TrackerHistory;
use SoapClient;

class PrePostagem implements TrackerContract
{
    /**
     * @var SoapClient
     */
    private $soap;
    /**
     * @var
     */
    private $user;
    /**
     * @var
     */
    private $pass;

    /**
     * PrePostagem constructor.
     * @param SoapClient $soap
     * @param $user
     * @param $pass
     */
    public function __construct(SoapClient $soap, $user, $pass)
    {
        $this->soap = $soap;
        $this->user = $user;
        $this->pass = $pass;
    }

    /**
     * @param $nfeNumber
     * @return TrackerDetails
     */
    public function findByNfe($nfeNumber)
    {
        $response = $this->soap->BuscaEtiquetaPorNF([
            'numeroNF' => $nfeNumber,
            'usuario' => $this->user,
            'senha'   => $this->pass
        ]);

        if(!isset( $response->BuscaEtiquetaPorNFResult->string )) {
            return false;
        }

        return $this->getCorreiosDetails( $response->BuscaEtiquetaPorNFResult->string );
    }

    private function getCorreiosDetails($trackNumber)
    {
        $correios = new CorreiosConsulta;
        $trackDetails = new TrackerDetails;
        $response = null;

        if (is_array($trackNumber)) {
            foreach($trackNumber as $number) {
                if( $response = $correios->rastrear( $number . "BR" ) ) {
                    $trackDetails->setTrackNumber($number . "BR");
                }
            }
        } else {
            $response = $correios->rastrear( $trackNumber . "BR" );
            $trackDetails->setTrackNumber($trackNumber . "BR");
        }

        if ($response) {
            foreach($response as $data){
                $trackDetails->addHistory(
                    new TrackerHistory(
                        isset($data['encaminhado']) ? $data['encaminhado'] : $data['status'],
                        $data['local'],
                        Carbon::createFromFormat("d/m/Y H:i", $data['data'])
                    )
                );
            }
            return $trackDetails;
        }

        return $trackDetails->addHistory(
            new TrackerHistory(
                "O nosso sistema não possui dados sobre o objeto informado. Por favor tente, novamente, mais tarde. ",
                null,
                Carbon::now()
            )
        );
    }
}