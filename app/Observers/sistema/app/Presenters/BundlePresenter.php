<?php

namespace App\Presenters;

use Laracasts\Presenter\Presenter;

class BundlePresenter extends Presenter
{
    private $categoryLabel = [
        "default"   => ["default", "fa fa-tag"],
        "upsell"    => ["primary", "fa fa-level-up"],
        "promotional"=> ["success", "fa fa-tags"],
        "remarketing"=> ["success", "fa fa-retweet"],
    ];

    /**
     * Calcula o desconto baseado no valor origianl dos produtos e no valor no pacote
     * @return string
     */
    public function discount()
    {
        return ((int) (100 - ($this->entity->price * 100 / $this->entity->old_price))) . '%';
    }

    /**
     * Exibe preço antigo baseado no valor do produto
     * @return string
     */
    public function oldPrice()
    {
        return monetary_format($this->entity->old_price);
    }

    /**
     * Exibe  preço novo baseado no valor do pacote
     * @return string
     */
    public function newPrice()
    {
        return monetary_format($this->entity->price);
    }

    /**
     * Exibe formas de parcelamento
     * @return string
     */
    public function installments()
    {
        $total = $this->entity->price;
        $installments = $this->entity->installments;

        return str_ireplace(" x", "x <span class='no-wrap'>", installments($total, $installments))."</span>";
    }

    /**
     * Exibe formas de parcelamento dependendo do tipo de pagamento
     * @param $payment
     * @return string
     */
    public function installmentsByPayment($payment)
    {
        if ($payment == 'creditcard') {
            return $this->installments();
        }

        return monetary_format($this->entity->price);
    }

    /**
     * Exibe formas de parcelamento do frete
     * @param $payment
     * @return string
     */
    public function installmentsFreightByPayment($payment)
    {
        if ($payment == 'creditcard') {
            return installments($this->entity->freight_value, $this->entity->installments);
        }

        return monetary_format($this->entity->freight_value);
    }

    /**
     * Exibe formas de parcelamento com o valor do frete
     * @param $payment
     * @return string
     */
    public function installmentsWithFreightByPayment($payment)
    {
        $total = $this->entity->freight_value + $this->entity->price;

        if ($payment == 'creditcard') {
            return installments($total, $this->entity->installments);
        }

        return monetary_format($total);
    }

    /**
     * Exibe formas de parcelamento com o valor do frete
     * @return string
     */
    public function installmentsWithFreight()
    {
        $total = $this->entity->freight_value + $this->entity->price;

        return installments($total, $this->entity->installments);
    }

    /**
     * Exibe formas de parcelamento com o valor do frete
     * @return string
     */
    public function installmentsFreight()
    {
        if( $this->entity->freight_value > 0) {
            return installments($this->entity->freight_value, $this->entity->installments);
        }

        return monetary_format(0);
    }

    /**
     * @return string
     */
    public function installmentValue()
    {
        return monetary_format($this->entity->price / $this->entity->installments);
    }

    public function freightValue()
    {
        return monetary_format( $this->entity->freight_value );
    }

    public function category()
    {
        $label = $this->categoryLabel[ $this->entity->category ];
        return "<label class='label label-".$label[0]."'>"
                    .mb_strtoupper($this->entity->category)
                    ." <i class='" . $label[1] . "'></i>"
               ."</label>";
    }
}