<?php

namespace App\Observers;

use App\Entities\Customer;
use App\Entities\ExternalServiceSettings;
use App\Services\SendToEvolux;
use App\Jobs\RemoveCustomerFromInterestCampaign;

class RemoveFromInterestCampaignObserver
{
    public function saving(Customer $customer)
    {
        if (auth()->user() && $customer->isDirty('document_number')) {
            $evolux_conf = ExternalServiceSettings::where([
                ['service','=','Evolux'],
                ['name', 'like', '%remover interessado%']
            ])->first();

            if (!is_null($evolux_conf)) {
                $job = new RemoveCustomerFromInterestCampaign($customer, $evolux_conf);
                dispatch($job);
            }
        }
    }
}
