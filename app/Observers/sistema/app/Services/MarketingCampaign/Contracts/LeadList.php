<?php

namespace App\Services\MarketingCampaign\Contracts;

interface LeadList
{
    /**
     * @param $listName
     * @return integer id of created list
     */
    public function create($listName);

    /**
     * @param $listId
     * @param $leadId
     * @return mixed
     */
    public function addLead($listId, $leadId);

    /**
     * @param $fromListId
     * @param $toListId
     * @param $leadId
     * @return mixed
     */
    public function updateLeadList($fromListId, $toListId, $leadId);

    /**
     * @param $listId
     * @param $leadId
     * @return mixed
     */
    public function removeLead($listId, $leadId);

    /**
     * @param $listName
     * @return mixed
     */
    public function find($listName);

    /**
     * @param $listName
     * @return mixed
     */
    public function findOrCreate($listName);
}