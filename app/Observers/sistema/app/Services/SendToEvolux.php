<?php

namespace App\Services;

use App\Entities\Customer;
use App\Entities\ExternalServiceSettings;
use GuzzleHttp\Client                     as HttpClient;
use GuzzleHttp\Exception\ClientException  as HttpClientException;
use GuzzleHttp\Exception\ConnectException as ConnectException;
use Monolog\Logger;

class SendToEvolux
{
    private $customer;

    private $evolux_conf;

    public function __construct(
        Customer                $customer,
        ExternalServiceSettings $evolux_conf
    ) {
        $this->customer    = $customer;
        $this->evolux_conf = $evolux_conf;
        $this->http_client = new HttpClient();
    }

    public function fire()
    {
        $phone = $this->validatePhone($this->customer->telephone);
        if ($phone === false) {
            $this->log(Logger::WARNING, 'invalid_phone');
            return false;
        }
        if (! in_array(
            $this->customer->site_id,
            $this->evolux_conf->sites()->get()->pluck('id')->toArray()
        )) {
            $this->log(Logger::INFO, 'site_not_checked');
            return false;
        }

        $form_params = [
            'name'        => $this->customer->fullName()                        ,
            'external_id' => $this->customer->hash                              ,
            'number'      => $phone                                             ,
            'custom_site' => $this->customer->site->name                        ,
            'custom_cart' => str_replace(';', "\n", $this->customer->custom_txt),
            'token'       => trim($this->evolux_conf->api_key)                  ,
        ];

        try {
            $res = $this->http_client->request('POST', trim($this->evolux_conf->base_url), [
                'form_params' => $form_params,
            ]);

            $this->log(Logger::INFO, 'simple_info', [
                'id|name'         => $this->customer->id .' | '. $this->customer->fullName(),
                'document_number' => $this->customer->document_number                        ,
                'last_order'      => '',
                'queue'           => $this->evolux_conf->name                                ,
                'site'            => $this->customer->site->name                             ,
            ]);
        } catch (HttpClientException $e) {
            $this->log(Logger::WARNING, 'queue_error', $e->getMessage(), $form_params);
        } catch (ConnectException $e) {
            $this->log(Logger::ERROR, 'network_fail', $e->getMessage(), $form_params);
        } catch (\Exception $e) {
            $this->log(Logger::ERROR, 'unexpected_error', $e->getMessage(), $form_params);
        }
    }

    public function removeFromCampaign()
    {
        $form_params =  [
            'token'       => trim($this->evolux_conf->api_key),
            'external_id' => $this->customer->hash              ,
        ];

        try {
            $res = $this->http_client->request('POST', trim($this->evolux_conf->base_url), [
                'form_params' => $form_params,
            ]);
        } catch (HttpClientException $e) {
            $this->log(Logger::NOTICE, 'client_not_found', $e->getMessage(), $form_params);
        } catch (ConnectException $e) {
            $this->log(Logger::ERROR, 'network_fail', $e->getMessage(), $form_params);
        } catch (\Exception $e) {
            $this->log(Logger::ERROR, 'unexpected_error', $e->getMessage(), $form_params);
        }
    }


    private function log($type, $title, $message = null, $params = null)
    {
        $info = [];

        foreach (['message', 'params'] as $var) {
            if (! is_null($$var)) {
                $info[$var] = $$var;
            }
        }

        $messages = [
            'invalid_phone'    => "Telefone inválido {". $this->customer->telephone ."}"                ,
            'site_not_checked' => "Site do produto {". $this->customer->site->name ."} ".
                                  "não marcado para a integracação em {".
                                   $this->evolux_conf->name ."}" ,
            'queue_error'      => "Fila ". $this->evolux_conf->name ." com erro:"                       ,
            'network_fail'     => "Falha de comunicação com o Evolux. Fila: ". $this->evolux_conf->name ,
            'unexpected_error' => "Um erro inesperado aconteceu. Fila: ". $this->evolux_conf->name      ,
            'client_not_found' => "Cliente não encontrado na Fila: ". $this->evolux_conf->name          ,
            'simple_info'      => "Dados :"
        ];

        \Log::getMonolog()->log($type, '[EVOLUX] '. $messages[$title], $info);
    }

    public function validatePhone($phone)
    {
        $phone  = preg_replace('/\D|\s/', '', $phone);
        $ddd    = substr($phone, 0, 2);
        $number = substr($phone, 2);

        if (in_array(strlen($phone), [10, 11])) {
            $elev = [];
            foreach (range(0, 11) as $num) {
                $elev[] = '';
            }

            for ($n = 0; $n < 10; $n++) {
                $eleven = join($n, $elev);
                $ten    = substr($eleven, 1);
                $nine   = substr($eleven, 2);
                $eight  = substr($eleven, 3);

                if (in_array($phone, [ $ten  , $eleven ]) or
                    in_array($number, [ $eight, $nine   ])) {
                    return false;
                }
            }

            if ($this->dddIsValid($ddd)) {
                $first = substr($number, 0, 1);
                if (strlen($number) == 9 or
                    (strlen($number) == 8 && in_array($first, [2, 3, 4, 5, 7]))) {
                    return $phone;
                } elseif (strlen($number) == 8 && in_array($first, [8,9])) {
                    return $ddd . '9' . $number;
                }
            }
        }
        return false;
    }

    private function dddIsValid($ddd)
    {
        return in_array($ddd, [
            11, 12, 13, 14, 15, 16, 17, 18, 19,
            21, 22, 24, 27, 28, 31, 32, 33, 34,
            35, 37, 38, 41, 42, 43, 44, 45, 46,
            47, 48, 49, 51, 53, 54, 55, 61, 62,
            64, 63, 65, 66, 67, 68, 69, 71, 73,
            74, 75, 77, 79, 81, 82, 83, 84, 85,
            86, 87, 88, 89, 91, 92, 93, 94, 95,
            96, 97, 98, 99
        ]);
    }
}
