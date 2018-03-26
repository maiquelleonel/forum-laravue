<?php
namespace App\Listeners;

use App\Entities\Order;
use App\Entities\ExternalServiceSettings;
use App\Events\OrderBilletCreated;
use App\Jobs\SendCustomerPhoneOrderPayByBilletToEvolux;

class SendOrderBilletPhoneToEvolux
{
    public function handle(OrderBilletCreated $orderBillet)
    {
        $customer    = $orderBillet->order->customer;
        $evolux_conf = ExternalServiceSettings::where([
            ['service','=','Evolux'],
            ['name', 'LIKE', '%boleto%'],
        ])->get()->first();
        // pesquisar se há outras tentativas de pagamento canceladas de cartao,
        // caso tenha outras tentativas via cartao, nao enviar para a campanha de boleto.
        if ( $evolux_conf &&
             $orderBillet->order->origin != 'system' &&
            !is_object($orderBillet->order->CanceledCreditCardTransaction) ){
                $job = new SendCustomerPhoneOrderPayByBilletToEvolux($customer, $evolux_conf);
                $job->delay(300); //5min
                dispatch($job);
        }
    }
}
