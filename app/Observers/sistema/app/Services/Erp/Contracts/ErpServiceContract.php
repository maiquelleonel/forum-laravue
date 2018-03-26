<?php

namespace App\Services\Erp\Contracts;

use App\Entities\ErpSetting;
use App\Entities\Order;
use App\Services\Erp\Response;
use Illuminate\Support\Collection;

interface ErpServiceContract
{
    /**
     * ErpServiceContract constructor.
     * @param ErpSetting $erpSetting
     */
    public function __construct(ErpSetting $erpSetting);

    /**
     * Retrieve NFE number by invoice number
     * @param $invoiceNumber
     * @return mixed
     */
    public function getNfeNumberByInvoice($invoiceNumber);

    /**
     * @param Order $order
     * @param bool $generateNfe
     * @return Response Invoice Number
     */
    public function sendOrder(Order $order, $generateNfe = false);

    /**
     * @param Order $order
     * @return Collection
     */
    public function validate(Order $order);
}