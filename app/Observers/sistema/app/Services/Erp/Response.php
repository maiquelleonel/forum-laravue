<?php
/**
 * Created by PhpStorm.
 * User: dlima
 * Date: 11/6/17
 * Time: 15:17
 */

namespace App\Services\Erp;


class Response
{
    private $invoiceId;
    private $invoiceNumber;
    private $trackCode;

    /**
     * Response constructor.
     * @param $invoiceId
     * @param $invoiceNumber
     * @param $trackCode
     */
    public function __construct($invoiceId = null, $invoiceNumber = null, $trackCode = null)
    {
        $this->invoiceId = $invoiceId;
        $this->invoiceNumber = $invoiceNumber;
        $this->trackCode = $trackCode;
    }

    /**
     * @return mixed
     */
    public function getInvoiceId()
    {
        return $this->invoiceId;
    }

    /**
     * @param mixed $invoiceId
     */
    public function setInvoiceId($invoiceId)
    {
        $this->invoiceId = $invoiceId;
    }

    /**
     * @return mixed
     */
    public function getInvoiceNumber()
    {
        return $this->invoiceNumber;
    }

    /**
     * @param mixed $invoiceNumber
     */
    public function setInvoiceNumber($invoiceNumber)
    {
        $this->invoiceNumber = $invoiceNumber;
    }

    /**
     * @return mixed
     */
    public function getTrackCode()
    {
        return $this->trackCode;
    }

    /**
     * @param mixed $trackCode
     */
    public function setTrackCode($trackCode)
    {
        $this->trackCode = $trackCode;
    }
}