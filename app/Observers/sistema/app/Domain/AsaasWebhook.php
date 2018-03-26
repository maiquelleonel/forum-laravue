<?php
/**
 * Created by PhpStorm.
 * User: dlima
 * Date: 3/9/16
 * Time: 3:06 PM
 */

namespace App\Domain;


class AsaasWebhook
{
    /**
     * Geração de nova cobrança
     */
    const PAYMENT_CREATED = "PAYMENT_CREATED";

    /**
     * Alteração no vencimento ou valor de cobrança existente.
     */
    const PAYMENT_UPDATED = "PAYMENT_UPDATED";

    /**
     * Cobrança autorizada pela adquirente (somente cartão de crédito)
     */
    const PAYMENT_CONFIRMED = "PAYMENT_CONFIRMED";

    /**
     * Cobrança recebida.
     */
    const PAYMENT_RECEIVED = "PAYMENT_RECEIVED";

    /**
     * Cobrança vencida
     */
    const PAYMENT_OVERDUE = "PAYMENT_OVERDUE";

    /**
     * Cobrança removida
     */
    const PAYMENT_DELETED = "PAYMENT_DELETED";

    /**
     * Cobrança estornada (somente cartão de crédito)
     */
    const PAYMENT_REFUNDED = "PAYMENT_REFUNDED";

}