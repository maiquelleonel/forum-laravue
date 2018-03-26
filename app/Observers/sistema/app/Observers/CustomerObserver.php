<?php

namespace App\Observers;

use App\Entities\Customer;
use App\Jobs\SendInterestedCustomerToEvolux;
use Carbon\Carbon;

class CustomerObserver
{
    public function creating(Customer $customer)
    {
        $now            = Carbon::now();
        $timestamp      = $now->timestamp;
        $_id            = strrev($now->format('dmYHis'));
        $microtime      = preg_replace('/\\D/', '', microtime());
        $customer->hash = "$_id-$timestamp-$microtime";
    }

    public function created(Customer $customer)
    {
        if (!auth()->user()) {
            $job = new SendInterestedCustomerToEvolux($customer);
            $job->delay(1200); //20min
            dispatch($job);
        }
    }
}
