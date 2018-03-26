<?php

namespace App\Services\Report;

use Carbon\Carbon;

class ReportResponse
{
    private $date;
    private $quantity;
    private $amount;

    /**
     * ReportResponse constructor.
     * @param $date
     * @param $quantity
     * @param $amount
     */
    public function __construct($date, $quantity, $amount)
    {
        $this->date = $date;
        $this->quantity = $quantity;
        $this->amount = $amount;
    }

    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param mixed $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return mixed
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param mixed $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param mixed $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }
}