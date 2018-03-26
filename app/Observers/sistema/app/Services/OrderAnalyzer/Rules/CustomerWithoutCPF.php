<?php
namespace App\Services\OrderAnalyzer\Rules;
use App\Entities\Order;
use App\Services\OrderAnalyzer\Contracts\OrderAnalyzerRuleContract;

class CustomerWithoutCPF implements OrderAnalyzerRuleContract
{

    /**
     * @param Order $order
     * @return boolean
     */
    public function passes(Order $order)
    {
        return strlen($order->customer->document_number) > 0;
    }

    /**
     * Get Rule Description
     * @return string
     */
    public function message()
    {
        return "Cliente sem CPF";
    }

    /**
     * Get Rule Name
     * @return string
     */
    public function name()
    {
        return "Valida CPF do cliente";
    }
}
