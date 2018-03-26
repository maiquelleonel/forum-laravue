<?php

namespace App\Domain;

class OrderStatus
{
    const APPROVED      = "aprovado";

    const AUTHORIZED    = "autorizado";

    const CANCELED      = "cancelado";

    const PENDING       = "pendente";

    const REFUND        = "estornado";

    const INTEGRATED    = "integrado";

    const PENDING_INTEGRATION = "pendente_integracao";

    const PENDING_INTEGRATION_IN_ANALYZE = "pendente_integracao_em_analise";

    public static function all($readable = false)
    {
        return self::readable([
            self::APPROVED      => self::APPROVED,
            self::AUTHORIZED    => self::AUTHORIZED,
            self::CANCELED      => self::CANCELED,
            self::PENDING       => self::PENDING,
            self::REFUND        => self::REFUND,
            self::INTEGRATED    => self::INTEGRATED,
            self::PENDING_INTEGRATION => self::PENDING_INTEGRATION,
            self::PENDING_INTEGRATION_IN_ANALYZE => self::PENDING_INTEGRATION_IN_ANALYZE,
        ], $readable);
    }

    public static function approved($readable = false)
    {
        return self::readable([
            self::APPROVED      => self::APPROVED,
            self::AUTHORIZED    => self::AUTHORIZED,
            self::INTEGRATED    => self::INTEGRATED,
            self::PENDING_INTEGRATION => self::PENDING_INTEGRATION,
            self::PENDING_INTEGRATION_IN_ANALYZE => self::PENDING_INTEGRATION_IN_ANALYZE
        ], $readable);
    }

    /**
     * Exibe lista de status permitidos para alteração a partir do status atual
     * @param $fromStatus
     * @return array
     */
    public static function allowChangeTo($fromStatus, $readable = false)
    {
        switch ($fromStatus) {
            case self::APPROVED:
            case self::PENDING_INTEGRATION:
            case self::PENDING_INTEGRATION_IN_ANALYZE:
                return self::changeFromApproved($readable);

            case self::AUTHORIZED:
                return self::changeFromAuthorized($readable);

            case self::INTEGRATED:
                return self::changeFromIntegrated($readable);

            case self::CANCELED:
                return self::changeFromCanceled($readable);

            case self::PENDING:
                return self::changeFromPending($readable);

            case self::REFUND:
                return self::changeFromRefund($readable);
        }

        return [];
    }

    private static function changeFromApproved($readable = false)
    {
        return self::readable([
            self::APPROVED      => self::APPROVED,
            self::INTEGRATED    => self::INTEGRATED,
            self::PENDING_INTEGRATION => self::PENDING_INTEGRATION,
            self::PENDING_INTEGRATION_IN_ANALYZE => self::PENDING_INTEGRATION_IN_ANALYZE,
            self::CANCELED      => self::CANCELED,
            self::REFUND        => self::REFUND,
            self::PENDING       => self::PENDING
        ], $readable);
    }

    private static function changeFromAuthorized($readable = false)
    {
        return self::readable([
            self::AUTHORIZED    => self::AUTHORIZED,
            self::INTEGRATED    => self::INTEGRATED,
            self::APPROVED      => self::APPROVED,
            self::PENDING_INTEGRATION => self::PENDING_INTEGRATION,
            self::PENDING_INTEGRATION_IN_ANALYZE => self::PENDING_INTEGRATION_IN_ANALYZE,
            self::CANCELED      => self::CANCELED,
            self::REFUND        => self::REFUND
        ], $readable);
    }

    private static function changeFromIntegrated($readable = false)
    {
        return self::readable([
            self::INTEGRATED    => self::INTEGRATED,
            self::APPROVED      => self::APPROVED,
            self::PENDING_INTEGRATION => self::PENDING_INTEGRATION,
            self::PENDING_INTEGRATION_IN_ANALYZE => self::PENDING_INTEGRATION_IN_ANALYZE,
            self::CANCELED      => self::CANCELED,
            self::REFUND        => self::REFUND,
            self::PENDING       => self::PENDING
        ], $readable);
    }

    private static function changeFromCanceled($readable = false)
    {
        return self::readable([
            self::CANCELED      => self::CANCELED,
            self::APPROVED      => self::APPROVED,
            self::PENDING_INTEGRATION => self::PENDING_INTEGRATION,
            self::PENDING_INTEGRATION_IN_ANALYZE => self::PENDING_INTEGRATION_IN_ANALYZE,
            self::AUTHORIZED    => self::AUTHORIZED,
            self::PENDING       => self::PENDING,
            self::REFUND        => self::REFUND
        ], $readable);
    }

    private static function changeFromPending($readable = false)
    {
        return self::readable([
            self::PENDING       => self::PENDING,
            self::APPROVED      => self::APPROVED,
            self::PENDING_INTEGRATION => self::PENDING_INTEGRATION,
            self::PENDING_INTEGRATION_IN_ANALYZE => self::PENDING_INTEGRATION_IN_ANALYZE,
            self::AUTHORIZED    => self::AUTHORIZED,
            self::CANCELED      => self::CANCELED,
            self::REFUND        => self::REFUND
        ], $readable);
    }

    private static function changeFromRefund($readable = false)
    {
        return self::readable([
            self::REFUND        => self::REFUND,
            self::APPROVED      => self::APPROVED,
            self::PENDING_INTEGRATION => self::PENDING_INTEGRATION,
            self::PENDING_INTEGRATION_IN_ANALYZE => self::PENDING_INTEGRATION_IN_ANALYZE,
            self::AUTHORIZED    => self::AUTHORIZED,
            self::CANCELED      => self::CANCELED,
            self::PENDING       => self::PENDING,
        ], $readable);
    }

    private static function readable($array, $readable = false)
    {
        if($readable){
            foreach($array as $key => &$value){
                $value = config("status." . $value . ".text");
            }
        }
        return $array;
    }

}
