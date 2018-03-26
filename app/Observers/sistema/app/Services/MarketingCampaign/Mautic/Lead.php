<?php

namespace App\Services\MarketingCampaign\Mautic;

use App\Entities\Customer;
use App\Entities\EmailCampaignSetting;
use App\Services\MarketingCampaign\Contracts\Lead as LeadContract;
use Mautic\Auth\ApiAuth;
use Mautic\MauticApi;

class Lead implements LeadContract
{
    /**
     * @var \Mautic\Auth\AuthInterface
     */
    protected $apiAuth;

    /**
     * @var \Mautic\Api\Contacts
     */
    protected $leadApi;

    /**
     * @var MauticApi
     */
    private $api;

    /**
     * @var EmailCampaignSetting
     */
    private $setting;

    /**
     * Lead constructor.
     * @param EmailCampaignSetting $setting
     */
    public function __construct(EmailCampaignSetting $setting)
    {
        $this->api = new MauticApi;
        $authData = ['userName' => $setting->username, 'password' => $setting->password ];
        $this->apiAuth  = (new ApiAuth)->newAuth( $authData, $setting->auth_type );
        $this->leadApi = $this->api->newApi("leads", $this->apiAuth, $setting->base_url);
        $this->setting = $setting;
    }

    /**
     * @inheritdoc
     */
    public function create(Customer $customer)
    {
        $response = $this->leadApi->create([
            'firstname'     => $customer->firstname,
            'lastname'      => $customer->lastname,
            'email'         => $customer->email,
            'zipcode'       => $customer->postcode,
            'address1'      => $customer->address_street.", ".$customer->address_street_number.", ".$customer->address_street_district,
            'address2'      => $customer->address_street_complement,
            'city'          => $customer->address_city,
            'state'         => trans("address.estados.".$customer->address_state)
        ]);

        return isset($response['contact']) ? ((object) $response['contact']) : false;
    }

    /**
     * @param Customer $customer
     * @return mixed
     */
    public function find(Customer $customer)
    {
        $leads = $this->leadApi->getList($customer->email);

        if (isset($leads['contacts'])) {
            foreach ($leads['contacts'] as $lead) {
                if (mb_strtolower($lead['fields']['all']['email']) == mb_strtolower($customer->email)) {
                    return (object) $lead;
                }
            }
        }

        return false;
    }

    /**
     * @param Customer $customer
     * @return mixed
     */
    public function findOrCreate(Customer $customer)
    {
        return $this->find($customer) ?: $this->create($customer);
    }
}