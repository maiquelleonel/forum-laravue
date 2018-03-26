<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Entities\Customer;
use App\Entities\ExternalServiceSettings;
use App\Services\SendToEvolux;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendInterestedCustomerToEvolux extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $customer;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Customer $customer)
    {
        $this->customer = $customer;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (is_null($this->customer->document_number) &&
            !is_object($this->customer->orders()->latest()->first())) {
            $evolux_conf = $this->customer->site->externalServices()->where([
                ['service','='   ,'Evolux']        ,
                ['name'   ,'LIKE','%interessados%'],
            ])->first();

            if ($evolux_conf) {
                $send = new SendToEvolux($this->customer, $evolux_conf);
                $send->fire();
            }
        }
    }
}
