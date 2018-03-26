<?php

namespace App\Services\OrderAnalyzer\Contracts;


use App\Entities\Order;

interface OrderAnalyzerRuleContract
{
    /**
     * @param Order $order
     * @return boolean
     */
    public function passes(Order $order);

    /**
     * Get Rule Name
     * @return string
     */
    public function name();

    /**
     * Get Rule Error/Success Message
     * @return string
     */
    public function message();
}