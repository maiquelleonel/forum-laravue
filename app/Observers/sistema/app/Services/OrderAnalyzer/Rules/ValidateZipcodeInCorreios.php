<?php

namespace App\Services\OrderAnalyzer\Rules;

use App\Entities\Order;
use App\Services\OrderAnalyzer\Contracts\OrderAnalyzerRuleContract;
use Correios;

class ValidateZipcodeInCorreios implements OrderAnalyzerRuleContract
{
    private $message;

    /**
     * @param Order $order
     * @return boolean
     */
    public function passes(Order $order)
    {
        //check Address is the same of API
        $result = false;
        $api_address   = $this->find($order->customer->postcode);

        if (!$api_address) {
            $this->message = "CEP informado não existe nos correios";
            return false;
        }

        if (str_contains($order->customer->address_city, "(")) {
            $this->message = "Campo Cidade contém caracteres inválidos";
            return false;
        }

        if (strlen($order->customer->postcode) < 8) {
            $this->message = "Formato de CEP inválido, formatos aceitos: 94000-999 ou 94000999";
            return false;
        }

        if (is_object($api_address)) {
            $api_address_text = [
                $api_address->uf,
                $this->hydrate($api_address->cidade),
            ];

            $customer_address_text = [
                $order->customer->address_state,
                $order->customer->address_city,
            ];

            if ($api_address->logradouro != "") {
                $api_address_text = array_merge($api_address_text, [
                    $api_address->bairro,
                    $api_address->logradouro,
                ]);

                $customer_address_text = array_merge($customer_address_text, [
                    $order->customer->address_street_district,
                    $order->customer->address_street,
                ]);
            }

            similar_text(
                strtolower(join(' ', array_reverse($customer_address_text))),
                strtolower(join(' ', array_reverse($api_address_text))),
                $percentual
            );

            if ($percentual >= 80) {
                $result = true;
            }
        }

        return $result;
    }

    private function hydrate($field)
    {
        if (str_contains($field, "(")) {
            return trim(explode("(", $field)[0]);
        }
        return $field;
    }

    /**
     * Get Rule Description
     * @return string
     */
    public function message()
    {
        if ($this->message) {
            return $this->message;
        }

        return "Endereço informado não bate com o CEP";
    }

    /**
     * Get Rule Name
     * @return string
     */
    public function name()
    {
        return "Checa CEP nos correios";
    }

    private function find($postcode)
    {
        $cep = Correios::cep($postcode);

        if (count($cep) === 0) {
            return null;
        }

        $cep = (Object) $cep;

        if (isset($cep->logradouro)) {
            $cep->logradouro = trim(preg_replace('/\(.+\)/', '', $cep->logradouro));
        }

        // Cep com numeração da rua, exemplo : "Rua abc - do 1 ao 100"
        if (isset($cep->logradouro) && str_contains($cep->logradouro, " - ")) {
            $cep->logradouro = explode(" - ", $cep->logradouro)[0];
        }

        // Cep de agência do correios, exemplo: Rua Adolfo Konder, 72 AC Concórdia
        if (isset($cep->logradouro) && str_contains($cep->logradouro, ",")) {
            $cep->logradouro = explode(",", $cep->logradouro)[0];
        }

        $parseString = function ($string) {
            return trim(str_ireplace(["%C2%A0", "+"], ["", " "], urlencode(strip_accents(strip_tags($string)))));
        };

        $cep->logradouro    = $parseString($cep->logradouro);
        $cep->bairro        = $parseString($cep->bairro);
        $cep->uf            = $parseString($cep->uf);
        $cep->cidade        = $parseString($cep->cidade);
        $cep->cep           = $parseString($cep->cep);

        return $cep;
    }
}
