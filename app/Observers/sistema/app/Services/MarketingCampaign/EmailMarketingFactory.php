<?php

namespace App\Services\MarketingCampaign;


use App\Entities\EmailCampaignSetting;

class EmailMarketingFactory
{
    /**
     * @param EmailCampaignSetting $setting
     * @return \Illuminate\Foundation\Application|mixed
     * @throws \Exception
     */
    public static function newLeadApi(EmailCampaignSetting $setting)
    {
        try {
            return app( __NAMESPACE__ . "\\" . $setting->service . "\\Lead", [$setting]);
        } catch (\Exception $e) {
            throw new \Exception("Service Email Marketing Campaign Not Found: " . $setting->service);
        }
    }

    /**
     * @param EmailCampaignSetting $setting
     * @return \Illuminate\Foundation\Application|mixed
     * @throws \Exception
     */
    public static function newLeadListApi(EmailCampaignSetting $setting)
    {
        try {
            return app( __NAMESPACE__ . "\\" . $setting->service . "\\LeadList", [$setting]);
        } catch (\Exception $e) {
            throw new \Exception("Service Email Marketing Campaign Not Found: " . $setting->service);
        }
    }
}