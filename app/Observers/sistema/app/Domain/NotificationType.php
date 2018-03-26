<?php

namespace App\Domain;

class NotificationType
{
    /**
     * Email types
     */
    const MAIL_ORDER_PAID        = "mail_order_paid";

    const MAIL_ORDER_AUTHORIZED  = "mail_order_authorized";

    const MAIL_ORDER_REFUND      = "mail_order_refund";

    const MAIL_ORDER_BILLET_CREATED = "mail_order_billet_created";

    /**
     * SMS types
     */
    const SMS_ORDER_PAID        = "sms_order_paid";

    const SMS_ORDER_AUTHORIZED  = "sms_order_authorized";

    const SMS_ORDER_REFUND      = "sms_order_refund";
}