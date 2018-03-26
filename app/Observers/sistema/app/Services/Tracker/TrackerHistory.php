<?php

namespace App\Services\Tracker;

use Carbon\Carbon;

class TrackerHistory
{
    /**
     * @var Carbon
     */
    private $date;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $local;

    /**
     * TrackerHistory constructor.
     * @param $status
     * @param $local
     * @param Carbon $date
     */
    public function __construct($status, $local, Carbon $date)
    {
        $this->status = $status;
        $this->local = $local;
        $this->date = $date;
    }

    /**
     * @return Carbon
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param Carbon $date
     */
    public function setDate(Carbon $date)
    {
        $this->date = $date;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getLocal()
    {
        return $this->local;
    }

    /**
     * @param string $local
     */
    public function setLocal($local)
    {
        $this->local = $local;
    }

}