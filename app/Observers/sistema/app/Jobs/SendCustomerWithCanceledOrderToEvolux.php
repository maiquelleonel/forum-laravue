<?php

namespace App\Jobs;

use App\Entities\Customer;
use App\Entities\Order;
use App\Entities\ExternalServiceSettings;
use App\Services\SendToEvolux;
use App\Domain\OrderStatus;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Carbon\Carbon;

class SendCustomerWithCanceledOrderToEvolux extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $customer;

    private $evolux_conf;

    public function __construct(Customer $customer)
    {
        $this->customer = $customer;
    }

    public function handle()
    {
        $statuses   = OrderStatus::approved();
        $statuses[] = OrderStatus::REFUND;
        $datetime5MinutesAgo = Carbon::now()->subMinutes(5);
        $customer_total_order_approved = Order::where([
            ['customer_id',             '=' , $this->customer->id ],
            ['created_at' ,             '>=', $datetime5MinutesAgo],
            ['payment_type_collection', '=' , 'creditcard'        ],
        ])->where(function ($q) {
            $q->orWhere('origin', '<>', 'system');
            $q->orWhere('origin'); //IS NULL
        })->whereIn('status', $statuses)->count();

        if ($customer_total_order_approved == 0) {
            // não funcionou armazenado no construct :(
            $evolux_conf = $this->customer->site->externalServices()->where([
                ['service','='   ,'Evolux'  ],
                ['name'   ,'LIKE','%cancel%'],
            ])->first();
            
            if ($evolux_conf) {
                $send = new SendToEvolux($this->customer, $evolux_conf);
                $send->fire();
            }
        }
    }
}
