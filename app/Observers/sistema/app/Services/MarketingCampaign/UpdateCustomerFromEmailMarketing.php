<?php

namespace App\Services\MarketingCampaign;

use App\Domain\LeadSegment;
use App\Entities\Customer;
use App\Entities\EmailCampaignContact;

class UpdateCustomerFromEmailMarketing extends BaseEmailMarketingCampaign
{
    /**
     * @param Customer $customer
     * @return mixed
     */
    public function fire(Customer $customer)
    {
        parent::fire($customer);

        $contact = $customer->emailCampaignContact;

        $prefix   = $customer->site->name . " - ";
        $newListName = $this->detectList( $customer );
        $newLeadList = $this->leadListApi->findOrCreate($prefix . $newListName);

        if(is_object($contact->list_id) && is_object($newLeadList->id) && is_object($contact->lead_id)){
            if($this->leadListApi->updateLeadList($contact->list_id, $newLeadList->id, $contact->lead_id)){
                return $customer->emailCampaignContact()->update([
                    "list_id"   => $newLeadList->id,
                    "list_name" => $newListName
                ]);
            }
        }

        return $customer->emailCampaignContact()->update([
            "list_name" => LeadSegment::ERROR
        ]);
    }
}