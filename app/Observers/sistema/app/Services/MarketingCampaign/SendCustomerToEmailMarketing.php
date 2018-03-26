<?php

namespace App\Services\MarketingCampaign;

use App\Domain\LeadSegment;
use App\Entities\Customer;
use App\Entities\EmailCampaignContact;

class SendCustomerToEmailMarketing extends BaseEmailMarketingCampaign
{
    /**
     * @param Customer $customer
     * @return mixed
     */
    public function fire(Customer $customer)
    {
        parent::fire($customer);

        $prefix   = $customer->site->name . " - ";
        $listName = $this->detectList( $customer );

        $lead     = $this->leadApi->findOrCreate($customer);
        $leadList = $this->leadListApi->findOrCreate($prefix . $listName);

        if ($lead && $leadList) {
            if($this->leadListApi->addLead($leadList->id, $lead->id)){
                return $customer->emailCampaignContact()->save(new EmailCampaignContact([
                    "lead_id"   => $lead->id,
                    "list_id"   => $leadList->id,
                    "list_name" => $listName
                ]));
            }
        }

        return $customer->emailCampaignContact()->save(new EmailCampaignContact([
            "list_name" => LeadSegment::ERROR
        ]));
    }
}