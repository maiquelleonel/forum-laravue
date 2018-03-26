<?php

namespace App\Jobs;

use App\Entities\Order;
use App\Entities\ExternalServiceSettings;
use App\Services\SendToEvolux;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendCustomerWithoutUpsellToEvolux extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $order;

    /**
     * Create a new job instance.
     *
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->order->userCanUpsell()) {
            $evolux_conf = ExternalServiceSettings::where([
                ['service','='   ,'Evolux'  ],
                ['name'   ,'LIKE','%upsell%'],
            ])->first();

            if ($evolux_conf && $evolux_conf->sites->count() > 0) {
                $send = new SendToEvolux($this->order->customer, $evolux_conf);
                $send->fire();
            }
        }
    }
}
