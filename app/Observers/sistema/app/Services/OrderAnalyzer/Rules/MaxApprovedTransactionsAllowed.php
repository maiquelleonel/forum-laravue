<?php
/**
 * Created by PhpStorm.
 * User: dlima
 * Date: 10/23/17
 * Time: 16:48
 */

namespace App\Services\OrderAnalyzer\Rules;

use App\Domain\TransactionType;
use App\Entities\Order;
use App\Entities\PaymentSetting;
use App\Entities\Transaction;
use App\Services\Gateways\Contracts\Boleto;
use App\Services\Gateways\Contracts\CreditCard;
use App\Services\OrderAnalyzer\Contracts\OrderAnalyzerRuleContract;
use App\Services\PagseguroTransaction;
use App\Support\SiteSettings;
use Illuminate\Support\Collection;

class MaxApprovedTransactionsAllowed implements OrderAnalyzerRuleContract
{
    private $order;

    private $approvedTransactions = [];

    private $customMessage;
    /**
     * @var SiteSettings
     */
    private $siteSettings;

    /**
     * MaxApprovedTransactionsAllowed constructor.
     * @param SiteSettings $siteSettings
     */
    public function __construct(SiteSettings $siteSettings)
    {
        $this->siteSettings = $siteSettings;
    }

    /**
     * @param Order $order
     * @return boolean
     */
    public function passes(Order $order)
    {
        $this->order = $order;

        $transactions = $order->transactions;

        foreach($order->approvedUpsellOrders as $upsellOrder){
            $transactions = $transactions->merge($upsellOrder->transactions);
        }

        $transactions = $this->filterTransactions($transactions)->sortByDesc("type");

        $this->approvedTransactions = collect();

        if ($transactions->count() == 0) {
            return false;
        }

        foreach($transactions as $transaction) {
            if($total = $this->validTransaction($transaction)){
                $this->approvedTransactions->push($total);
            }
        }

        if (number_format($this->getTotalOrders(), 0) == number_format($this->approvedTransactions->sum(), 0)) {
            return true;
        }

        return false;
    }

    private function getTotalOrders()
    {
        $total = $this->order->sub_total;
        foreach($this->order->approvedUpsellOrders as $upsellOrder){
            $total += $upsellOrder->sub_total;
        }
        return $total;
    }

    /**
     * Get Rule Description
     * @return string
     */
    public function message()
    {
        if ($this->customMessage) {
            return $this->customMessage;
        }

        if ($this->approvedTransactions->count() === 0) {
            return "Pedido com nenhuma transação aprovada";
        }

        if (number_format($this->getTotalOrders(), 0) != number_format($this->approvedTransactions->sum(), 0)) {
            $totalOrder         = $this->getTotalOrders();
            $totalTransaction   = $this->approvedTransactions->sum();

            return "Valores não coincidem: Pedidos " . monetary_format($totalOrder) . " | Transações " . monetary_format($totalTransaction);
        }

        return "OK";
    }

    /**
     * Get Rule Name
     * @return string
     */
    public function name()
    {
        return "Checa Transações";
    }

    /**
     * @param Collection $transactions
     * @return Collection
     */
    private function filterTransactions($transactions)
    {
        return $transactions->filter(function($transaction){
            if (str_contains($transaction->response_json, '"CreditCardTransactionStatus":"AuthorizedPendingCapture"') OR
                str_contains($transaction->response_json, '"CreditCardTransactionStatus":"Captured"') OR
                str_contains($transaction->response_json, '"CreditCardTransactionStatus":"Voided"') OR
                str_contains($transaction->response_json, '"CreditCardTransactionStatus":"Refunded"') OR
                str_contains($transaction->response_json, '"SeverityCode":"Error"') OR
                $transaction->response_json == '{}' OR
                ($transaction->pay_reference != '' && !is_null($transaction->pay_reference)) OR
                $transaction->type == TransactionType::BOLETO OR
                $transaction->type == TransactionType::PAGSEGURO) {
                return true;
            }
            return false;
        });
    }

    /**
     * @param Transaction $transaction
     * @return float
     */
    private function validTransaction(Transaction $transaction)
    {
        switch($transaction->type){
            case TransactionType::CARTAO:
                return $this->creditCardTransactionIsApproved($transaction);

            case TransactionType::PAGSEGURO:
                return $this->pagSeguroTransactionIsApproved($transaction);

            case TransactionType::BOLETO:
                return $this->boletoTransactionIsApproved($transaction);
        }
        return false;
    }

    private function creditCardTransactionIsApproved(Transaction $transaction)
    {
        $gateway = $this->getCreditCardGateway($transaction->order);
        if( $storeToken = $transaction->getTransaction()->getStoreToken() ){
            if( $paymentKey = $transaction->getTransaction()->getPaymentKey() ){
                $response = $gateway->findByKey( $paymentKey, $storeToken);
            } else {
                $response = $gateway->findByReference( $transaction->pay_reference, $storeToken );
            }
        } else {
            if( $paymentKey = $transaction->getTransaction()->getPaymentKey() ){
                $response = $gateway->findByKey( $paymentKey );
            } else {
                $response = $gateway->findByReference( $transaction->pay_reference );
            }
        }

        if( $response->getTransactionStatus() == "Captured" ){
            return $response->getTotalValue();
        }

        if( $response->getTransactionStatus() == "PartialRefunded" ) {

            $transactionValue = $response->getTotalValue()-$response->getRefundedValue();
            return $transactionValue;
        }

        if ($response->getTransactionStatus() == "PartialCapture") {
            return $response->getTotalValue();
        }

        return 0;

    }

    private function pagSeguroTransactionIsApproved($transaction)
    {
        // PagSeguro Venda Digitada
        if (isset($transaction->request->key)) {
            /** @var $gateway PagseguroTransaction */
            $gateway = app(PagseguroTransaction::class);
            $response = $gateway->findTransaction( $transaction->request->key );
            if( $response->getStatus() ) {
                return $transaction->getTransaction()->getTotal();
            } else {
                return 0;
            }
        }

        // TODO VALIDAR TRANSAÇÕES PAGSEGURO DO SITE
        // DEFAULT APROVAR TODAS AS TRANSAÇÕES VIA SITE
        return $transaction->getTransaction()->getTotal();
    }

    private function boletoTransactionIsApproved($transaction)
    {
        $gateway = $this->getBoletoGateway($transaction->order);
        $response = $gateway->findBillet($transaction->pay_reference);
        if($response->getStatus()){
            return $transaction->getTransaction()->getTotal();
        }
        return 0;
    }

    /**
     * @param Order $order
     * @return CreditCard
     */
    private function getCreditCardGateway(Order $order)
    {
        return app( $this->getGatewayName($order, "CreditCard"), [
            $this->getPaymentSetting($order)
        ] );
    }

    /**
     * @param Order $order
     * @return Boleto
     */
    private function getBoletoGateway(Order $order)
    {
        return app( $this->getGatewayName($order, "Boleto"), [
            $this->getPaymentSetting($order)
        ] );
    }

    /**
     * @param Order $order
     * @param $gatewayType
     * @return CreditCard
     */
    private function getGatewayName(Order $order, $gatewayType)
    {
        $paymentSetting = $this->getPaymentSetting($order);

        switch($gatewayType){
            case "Boleto":
                return config( "payment.gateways.{$gatewayType}.{$paymentSetting->billet_gateway}");

            case "CreditCard":
                return config( "payment.gateways.{$gatewayType}.{$paymentSetting->creditcard_gateway}");
        }
    }

    /**
     * @param Order $order
     * @return PaymentSetting
     */
    private function getPaymentSetting(Order $order)
    {
        return $order->origin === "system"
                ? $this->siteSettings->getCallCenterPaymentSettings()
                : $this->siteSettings->getPaymentSettings();
    }
}