<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Entities\Customer;
use App\Entities\ExternalServiceSettings;
use App\Services\SendToEvolux;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class RemoveCustomerFromInterestCampaign extends Job implements ShouldQueue
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
        $evolux = new SendToEvolux($this->customer, $this->evolux_conf);
        $evolux->removeFromCampaign();
    }
}
