<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Entities\Customer;
use App\Entities\ExternalServiceSettings;
use App\Entities\Order;
use App\Domain\OrderStatus;
use App\Services\SendToEvolux;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Carbon\Carbon;

class SendCustomerPhoneOrderPayByBilletToEvolux extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $customer;

    private $evolux_conf;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Customer $customer, ExternalServiceSettings $evolux_conf)
    {
        $this->customer    = $customer;
        $this->evolux_conf = $evolux_conf;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $statuses   = OrderStatus::approved();
        $statuses[] = OrderStatus::REFUND;
        $datetime5MinutesAgo = Carbon::now()->subMinutes(5);
        $customer_total_order_approved = Order::where([
            ['customer_id',            '=' , $this->customer->id ],
            ['created_at' ,            '>=', $datetime5MinutesAgo],
            ['payment_type_collection','=' , 'creditcard'        ],
        ])->where(function ($q) {
            $q->orWhere('origin', '<>', 'system');
            $q->orWhere('origin'); //IS NULL
        })->whereIn('status', $statuses)->count();

        if ($customer_total_order_approved == 0 && $this->evolux_conf) {
            $send = new SendToEvolux($this->customer, $this->evolux_conf);
            $send->fire();
        }
    }
}
