<?php

namespace App\Services\Tracker;

interface TrackerContract
{
    /**
     * @param $nfeNumber
     * @return TrackerDetails
     */
    public function findByNfe($nfeNumber);
}