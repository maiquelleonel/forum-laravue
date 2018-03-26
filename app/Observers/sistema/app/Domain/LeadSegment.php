<?php

namespace App\Domain;


class LeadSegment
{
    /**
     * Clientes que não tem nenhum pedido
     */
    const INTERESSADO = "Interessados";

    /**
     * Clientes que tiveram o pagamento negado
     */
    const PAGAMENTO_NAO_APROVADO = "Pagamento Não Aprovado";

    /**
     * Clientes que pagaram mas não fizeram upsell
     */
    const CLIENTE_SEM_UPSELL = "Cliente Sem Upsell";

    /**
     * Clientes que pagaram e fizeram upsell
     */
    const CLIENTE_COM_UPSELL = "Cliente Com Upsell";

    /**
     * Clientes que emitiram boleto ou iniciaram transações via pagsguro
     */
    const PAGAMENTO_PENDENTE = "Pagamento Pendente";

    /**
     * Clientes com falha na integração
     */
    const ERROR = "Error";
}