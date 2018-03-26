<?php
namespace App\Services\OrderAnalyzer\Rules;
use App\Entities\Order;
use App\Services\OrderAnalyzer\Contracts\OrderAnalyzerRuleContract;

class ValidateAddressStreetNumber implements OrderAnalyzerRuleContract
{

    /**
     * @param Order $order
     * @return boolean
     */
    public function passes(Order $order)
    {
        $number = $order->customer->address_street_number;
        return preg_replace('/\d+|-|sn/i','',$number) == '';
    }

    /**
     * Get Rule Description
     * @return string
     */
    public function message()
    {
        return "Campo número do 'endereço de entrega' contém letra(s)";
    }

    /**
     * Get Rule Name
     * @return string
     */
    public function name()
    {
        return "Valida campo número do 'endereço de entrega'";
    }
}
