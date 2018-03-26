<?php
namespace App\Services\OrderAnalyzer\Rules;
use App\Entities\Order;
use App\Services\OrderAnalyzer\Contracts\OrderAnalyzerRuleContract;

class OrderWithoutApp implements OrderAnalyzerRuleContract
{

    /**
     * @param Order $order
     * @return boolean
     */
    public function passes(Order $order)
    {
        if($order->hasIPI()){
            return $order->hasApp();
        }
        return true;
    }

    /**
     * Get Rule Description
     * @return string
     */
    public function message()
    {
        return "Pedido contém itens com IPI mas nenhum Aplicativo";
    }

    /**
     * Get Rule Name
     * @return string
     */
    public function name()
    {
        return "Valida itens com IPI e APP";
    }
}
