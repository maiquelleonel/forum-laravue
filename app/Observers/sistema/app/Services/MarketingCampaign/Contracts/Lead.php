<?php

namespace App\Services\MarketingCampaign\Contracts;

use App\Entities\Customer;

interface Lead
{
    /**
     * @param Customer $customer
     * @return mixed
     */
    public function create(Customer $customer);

    /**
     * @param Customer $customer
     * @return mixed
     */
    public function find(Customer $customer);

    /**
     * @param Customer $customer
     * @return mixed
     */
    public function findOrCreate(Customer $customer);
}