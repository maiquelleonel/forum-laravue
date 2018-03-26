<?php

namespace App\Services\MarketingCampaign\Mautic;

use App\Entities\EmailCampaignSetting;
use App\Services\MarketingCampaign\Contracts\LeadList as LeadListContract;
use Mautic\Auth\ApiAuth;
use Mautic\MauticApi;

class LeadList implements LeadListContract
{
    /**
     * @var \Mautic\Api\Lists
     */
    private $listsApi;

    /**
     * @var MauticApi
     */
    private $api;

    /**
     * @var ApiAuth
     */
    private $apiAuth;

    /**
     * @var EmailCampaignSetting
     */
    private $setting;

    /**
     * LeadList constructor.
     * @param EmailCampaignSetting $setting
     */
    public function __construct(EmailCampaignSetting $setting)
    {
        $this->api = new MauticApi;
        $authData = ['userName' => $setting->username, 'password' => $setting->password ];
        $this->apiAuth  = (new ApiAuth)->newAuth( $authData, $setting->auth_type );
        $this->listsApi = $this->api->newApi("lists", $this->apiAuth, $setting->base_url);
        $this->setting = $setting;
    }

    /**
     * @param $listName
     * @return integer id of created list
     */
    public function create($listName)
    {
        $response = $this->listsApi->create([
            "name" => $listName,
            "alias"=> str_slug($listName)
        ]);

        return isset($response['list']) ? ( (object) $response['list'] ) : false;
    }

    /**
     * @param $listName
     * @return mixed
     */
    public function find($listName)
    {
        $listName = str_slug( $listName );

        $lists = $this->listsApi->getList( $listName );

        if (isset($lists['lists'])) {
            foreach ($lists['lists'] as $list) {
                if ($list['alias'] == $listName) {
                    return (object) $list;
                }
            }
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function findOrcreate($listName)
    {
        return $this->find($listName) ?: $this->create($listName);
    }

    /**
     * @inheritdoc
     */
    public function addLead($listId, $leadId)
    {
        return $this->listsApi->addLead($listId, $leadId);
    }

    /**
     * @param $listId
     * @param $leadId
     * @return mixed
     */
    public function removeLead($listId, $leadId)
    {
        return $this->listsApi->removeLead($listId, $leadId);
    }

    /**
     * @param $fromListId
     * @param $toListId
     * @param $leadId
     * @return mixed
     */
    public function updateLeadList($fromListId, $toListId, $leadId)
    {
        return $this->removeLead($fromListId, $leadId) && $this->addLead($toListId, $leadId);
    }
}