<?php

namespace App\Services\Tracker;

use Illuminate\Support\Collection;

class TrackerDetails
{
    private $history;

    private $trackNumber;

    public function __construct()
    {
        $this->history = new Collection;
    }

    /**
     * @param TrackerHistory $history
     * @return $this
     */
    public function addHistory(TrackerHistory $history)
    {
        $this->history->push( $history );
        return $this;
    }

    /**
     * @return Collection
     */
    public function getHistory()
    {
        return $this->history;
    }

    /**
     * @return string
     */
    public function getTrackNumber()
    {
        return $this->trackNumber;
    }

    /**
     * @param string $trackNumber
     * @return $this
     */
    public function setTrackNumber($trackNumber)
    {
        $this->trackNumber = $trackNumber;
        return $this;
    }
}