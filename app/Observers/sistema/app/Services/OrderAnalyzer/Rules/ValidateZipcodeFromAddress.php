<?php

namespace App\Services\OrderAnalyzer\Rules;

use App\Entities\Order;
use App\Services\OrderAnalyzer\Contracts\OrderAnalyzerRuleContract;
use Canducci\Cep\Endereco as ZipFinder;
use Canducci\Cep\CepClient;

class ValidateZipcodeFromAddress implements OrderAnalyzerRuleContract
{
    private $cep_api;

    private $error_message;

    /**
     * @param Order $order
     * @return boolean
     */
    public function passes(Order $order)
    {
        $this->cep_api = null;

        if (str_contains($order->customer->address_city, "(")) {
            $this->error_message = "Campo Cidade contém caracteres inválidos";
            return false;
        }

        $zipfinder = new ZipFinder(new CepClient());

        $api_zip = $zipfinder->find(
            $this->replaceSpaces($order->customer->address_state),
            $this->replaceSpaces($order->customer->address_city),
            $this->replaceSpaces(str_ireplace(".", "", $order->customer->address_street), "+")
        )->toArray()->result();

        $customerPostcode = $order->customer->postcode;

        // Encontrado mais de um CEP para o endereço, comparar pelo Bairro
        if (count($api_zip) > 1) {
            foreach ($api_zip as $zip) {
                if (isset($zip['cep'])) {
                    if ($this->compareCEP($zip['cep'], $customerPostcode)) {
                        return true;
                    }
                }
            }

            // Encontrou apenas 1 CEP
        } elseif (count($api_zip) == 1) {
            if (isset($api_zip[0]['cep'])) {
                if (isset($api_zip[0]['unidade']) && strlen($api_zip[0]['unidade']) > 0) {
                    return true;
                }
                return $this->compareCEP($api_zip[0]['cep'], $customerPostcode);
            }
        }

        return false;
    }

    private function compareCEP($apiCEP, $customerCEP)
    {
        $apiCEP = preg_replace('/-/', '', $apiCEP);
        $customerCEP = preg_replace('/-/', '', $customerCEP);
        $this->cep_api = $apiCEP;

        if ($apiCEP == $customerCEP) {
            return true;
        }
        return false;
    }

    /**
     * Get Rule Description
     * @return string
     */
    public function message()
    {
        if ($this->error_message) {
            return $this->error_message;
        }
        if ($this->cep_api) {
            return "O CEP do endereço informado não é o mesmo que o informado pelos correios ({$this->cep_api})";
        }
        return "Nenhum CEP localizado com o endereço informado";
    }

    /**
     * Get Rule Name
     * @return string
     */
    public function name()
    {
        return "Valida o CEP do endereço informado";
    }

    private function replaceSpaces($string, $replaceWith = "%20")
    {
        return str_ireplace(" ", $replaceWith, $string);
    }
}
