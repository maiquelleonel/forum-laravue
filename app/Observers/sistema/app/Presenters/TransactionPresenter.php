<?php

namespace App\Presenters;

use App\Domain\TransactionType;
use App\Services\Payment\Response\Boleto;
use App\Services\Payment\Response\CreditCard;
use App\Services\Payment\Response\PagSeguro;
use App\Services\Payment\Response\PayPal;
use App\Services\Payment\Response\PayPalResponse;

class TransactionPresenter extends BasePresenter
{
    /**
     * @var CreditCard|Boleto
     */
    private $transaction;

    public function __construct($entity)
    {
        parent::__construct($entity);

        switch ($this->entity->type) {
            case TransactionType::BOLETO:
                $this->transaction = new Boleto($this->entity);
                break;

            case TransactionType::PAGSEGURO:
                $this->transaction = new PagSeguro($this->entity);
                break;

            case TransactionType::PAYPAL:
                $this->transaction = new PayPal($this->entity);
                break;

            case TransactionType::CARTAO:
                $this->transaction = new CreditCard($this->entity);
                break;
        }
    }

    public function cardBrand()
    {
        return $this->transaction->getBrand();
    }

    public function cardNumber()
    {
        return $this->transaction->getCardNumber();
    }

    public function boletoLink()
    {
        return $this->transaction->getLink();
    }

    public function boletoDueDate()
    {
        return $this->transaction->getDueDate()->format("d/m/Y");
    }

    public function value () {
        return monetary_format( $this->transaction->getTotal() );
    }

    public function status ()
    {
        return $this->transaction->getStatus();
    }

    public function response()
    {
        return "<code>" .
                    str_ireplace("\\/", "/", strip_tags(json_encode($this->entity->response, JSON_PRETTY_PRINT))).
               "</code>";
    }

    public function request()
    {
        return "<code>" .
                    str_ireplace("\\/", "/", json_encode($this->entity->request, JSON_PRETTY_PRINT)).
               "</code>";
    }

    public function links()
    {
        $links = [];

        foreach ( $this->transaction->getLinks() as $text => $link ) {
            $links[] = \Html::decode( link_to($link->url, $text, $link->attributes) );
        }

        return implode(" " , $links);
    }
}