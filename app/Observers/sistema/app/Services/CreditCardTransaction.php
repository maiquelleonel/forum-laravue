<?php

namespace App\Services;

use App\Domain\OrderStatus;
use App\Entities\Additional;
use App\Entities\Bundle;
use App\Entities\Customer;
use App\Entities\Order;
use App\Entities\OrderItemProduct;
use App\Entities\PaymentSetting;
use App\Entities\Site;
use App\Entities\Status;
use App\Domain\TransactionType;
use App\Events\OrderUpSold;
use App\Repositories\Orders\OrderRepository;
use App\Repositories\Transaction\TransactionRepository;
use App\Services\Gateways\Contracts\CreditCard;
use App\Services\Gateways\PaymentResponse;
use App\Services\Order\CreateOrderService;
use App\Support\SiteSettings;

class CreditCardTransaction
{
    /**
     * @var CreateOrderService
     */
    private $createOrderService;
    /**
     * @var TransactionRepository
     */
    private $transactionRepository;
    /**
     * @var OrderRepository
     */
    private $orderRepository;
    /**
     * @var SiteSettings
     */
    private $siteSettings;

    /**
     * @var PaymentSetting
     */
    private $paymentSettings;

    /**
     * @var PaymentSetting
     */
    private $callCenterPaymentSettings;

    /**
     * CreditCardTransaction constructor.
     * @param OrderRepository $orderRepository
     * @param CreateOrderService $createOrderService
     * @param TransactionRepository $transactionRepository
     * @param SiteSettings $siteSettings
     * @internal param CreditCard $creditCard
     */
    public function __construct(
        OrderRepository $orderRepository,
        CreateOrderService $createOrderService,
        TransactionRepository $transactionRepository,
        SiteSettings $siteSettings
    ) {
        $this->createOrderService       = $createOrderService;
        $this->transactionRepository    = $transactionRepository;
        $this->orderRepository          = $orderRepository;
        $this->siteSettings             = $siteSettings;
        $this->paymentSettings          = $siteSettings->getPaymentSettings();
        $this->callCenterPaymentSettings= $siteSettings->getCallCenterPaymentSettings();
    }

    /**
     * Use this to Specific Payment Settings
     * @param Site $site
     * @return $this
     */
    public function setPaymentsFromSite(Site $site)
    {
        $this->paymentSettings          = $site->paymentSetting;
        $this->callCenterPaymentSettings= $site->callCenterPaymentSetting ?: $site->paymentSetting;
        return $this;
    }

    /**
     * @param array $card
     * @param Order $order
     * @param null $installments
     * @param null $origin
     * @param bool $useAntiFraud
     * @return PaymentResponse
     */
    public function payOrder(array $card, $order, $installments = null, $origin = null, $useAntiFraud = true, $payoutTogether = false, $fromCallCenter = false)
    {
        $order->installments = $installments;
        $order->origin = $origin;

        if ($this->siteSettings->getSite()->auto_refund == 1 or
            (isset($card['auto_refund']) && $card['auto_refund'] === true)) {
            return $this->payAndRefund($card, $order, $installments, $origin);
        }

        $transaction = $this->makeTransaction($order, $card, "", $useAntiFraud, $payoutTogether, $fromCallCenter);

        if ($transaction->getStatus()) {
            $order->payment_type_collection  = 'CreditCard';
            $order->payment_type             = $this->getGatewayName($fromCallCenter);

            if (!$order->user_id && auth()->user()) {
                $order->user_id = auth()->user()->id;
            }

            $order->save();
        }

        if ($payoutTogether
                && $order->upsellOrder
                && $transaction->getStatus()
                && $order->upsellOrder->isCreditCard()
                && $order->upsellOrder->isPaid()) {
            $this->refundOrder($order->upsellOrder);
        }

        $this->updateOrderStatusByTransactionStatus($order, $transaction);

        return $transaction;
    }

    private function payAndRefund(array $card, $order, $installments = null, $origin = null, $fromCallCenter = false)
    {
        $order->payment_type_collection  = 'CreditCard';
        $order->payment_type             = $this->getGatewayName($fromCallCenter);

        $transactionApproved = $this->transact($order, $card, '_TEST', false, false);

        if ($transactionApproved->getTransactionToken() && $transactionApproved->getStoreToken()) {
            //estorna o pedido automaticamente
            $creditCard  = $this->getGateway($fromCallCenter);
            $transaction = $creditCard->makeCancel(
                $transactionApproved->getTransactionToken(),
                $transactionApproved->getStoreToken()
            );
            $this->registerTransaction($transaction, $order, $transactionApproved->getIdentifier());
        }

        $order->status = OrderStatus::CANCELED;
        $order->save();

        return new PaymentResponse(false, 400, "Infelizmente nosso estoque acabou. Não se preocupe, sua compra foi estornada. Entraremos em contato assim que nosso estoque for renovado.", null, null, null);
    }

    public function refundOrder(Order $order)
    {
        $transactionApproved = $order->lastValidCreditCardPayment();

        if ($transactionApproved->getTransactionToken() && $transactionApproved->getStoreToken()) {
            $creditCard = $this->getGateway(false);
            $transaction = $creditCard->makeCancel(
                $transactionApproved->getTransactionToken(),
                $transactionApproved->getStoreToken()
            );
            $this->registerTransaction($transaction, $order, $transactionApproved->getIdentifier());
            return $transaction;
        }

        return new PaymentResponse(false, 404, "Pedido não tem transações válidas", null, null, null);
    }

    /**
     * @param $card
     * @param Bundle $bundle
     * @param Customer $customer
     * @return Gateways\PaymentResponse
     */
    public function create(array $card, Bundle $bundle, Customer $customer, $installments = null, $origin = null)
    {
        // CRIA OU ATUALIZA OS DADOS DO PEDIDO
        if ($orderId = session()->get("order_id")) {
            $order = $this->orderRepository->find($orderId);
            $order = $this->updateOrder($order, $bundle, $installments, $origin);
        } else {
            $order = $this->createOrder($bundle, $customer, $installments, $origin);
        }

        session()->put("order_id", $order->id);

        return $this->payOrder($card, $order, $installments, $origin);
    }

    /**
     * @param Order $order
     * @return Gateways\PaymentResponse
     */
    public function upSellFromOrder(Order $order)
    {
        if ($order->canUpsell()) {

            $lastTransaction = $order->lastValidCreditCardPayment();

            if (!$lastTransaction
                OR !$lastTransaction->getPaymentToken()
                OR !$lastTransaction->getTransactionToken()
                OR !$lastTransaction->getStoreToken()) {
                return false;
            }

            $paidBundle     = $order->bundles->first();
            $upsellBundle   = $paidBundle->upsell->first();

            // Order
            $upsellOrder = clone $order;
            $upsellOrder->total         = $upsellBundle->price;
            $upsellOrder->freight_value = $upsellBundle->freight_value;
            $upsellOrder->installments  = $upsellBundle->installments;

            // Items
            $upsellItem = $upsellOrder->itemsBundle->first();
            $upsellItem->price = $upsellBundle->price;
            $upsellItem->bundle_id = $upsellBundle->id;
            $upsellItem->products = $upsellBundle->products;

            $transaction = $this->makeTransaction($upsellOrder, $lastTransaction->getPaymentToken(), "_UPSELL");

            if ($transaction->getStatus()) {
                // Cancel first order after process upsell
                $creditCard = $this->getGateway();
                $transaction = $creditCard->makeCancel(
                    $lastTransaction->getTransactionToken(),
                    $lastTransaction->getStoreToken()
                );
                $this->registerTransaction($transaction, $upsellOrder, $lastTransaction->getIdentifier());

                $this->updateOrder($order, $upsellBundle, $upsellBundle->installments);
                event(new OrderUpSold($order));
            }

            return $transaction;
        }

        return false;
    }

    /**
     * @param Order $order
     * @param Additional $additional
     * @param integer $qty
     * @return bool
     */
    public function additionalFromOrder($order, $additional, $qty)
    {
        if ($additional) {
            $lastTransaction = $order->lastValidCreditCardPayment();

            if (!$lastTransaction
                OR !$lastTransaction->getPaymentToken()
                OR !$lastTransaction->getTransactionToken()
                OR !$lastTransaction->getStoreToken()) {
                return false;
            }

            // Order
            $newOrder = clone $order;
            $newOrder->total = $order->total + ($additional->price * $qty);
            $newOrder->itemsProduct->push(new OrderItemProduct([
                "qty"   => $qty,
                "price" => $additional->price,
                "product_id" => $additional->product->id
            ]));

            $transaction = $this->makeTransaction($newOrder, $lastTransaction->getPaymentToken(), "_ADDITIONAL");

            if ($transaction->getStatus()) {

                $creditCard = $this->getGateway();
                $transaction = $creditCard->makeCancel(
                    $lastTransaction->getTransactionToken(),
                    $lastTransaction->getStoreToken()
                );
                $this->registerTransaction($transaction, $newOrder, $lastTransaction->getIdentifier());

                $order->total = $newOrder->total;
                $order->save();

                $this->createOrderService->attachProduct(
                    $additional->product, $order, $qty, $additional->price
                );

                event(new OrderUpSold($order));

                return $transaction;
            }
        }

        return false;
    }

    /**
     * @param Bundle $bundle
     * @param Customer $customer
     * @return mixed
     */
    private function createOrder(Bundle $bundle, Customer $customer, $installments = null, $origin = null)
    {
        return $this->createOrderService
            ->create($customer, $bundle->price, $bundle->freight_value, Status::PENDING, [$bundle], 'CreditCard', $this->getGatewayName(), $installments, $origin);
    }

    /**
     * @param Order $order
     * @param Bundle $bundle
     * @param int $installments
     * @param string $origin
     * @return Order|mixed
     */
    private function updateOrder(Order $order, Bundle $bundle, $installments = null, $origin = null)
    {
        return $this->createOrderService
            ->update($order, $bundle->price, $bundle->freight_value, $order->status, [$bundle], 'CreditCard', $this->getGatewayName(), $installments, $origin);
    }

    /**
     * @param $order
     * @param PaymentResponse $response
     * @return mixed
     */
    private function updateOrderStatusByTransactionStatus($order, PaymentResponse $response)
    {
        if ($response->getPaymentMethod() == "AuthAndCapture") {
            $status = OrderStatus::APPROVED;
        } else {
            $status = OrderStatus::AUTHORIZED;
        }

        return $this->orderRepository
            ->update(['status' => $response->getStatus() ? $status : Status::CANCELED], $order->id);
    }

    /**
     * @param Order $order
     * @param $card
     * @param string $referenceAppend
     * @param bool $useAntiFraud
     * @param bool $payoutTogether
     * @param bool $fromCallCenter
     * @return PaymentResponse
     */
    private function makeTransaction(Order $order, $card, $referenceAppend = "", $useAntiFraud = true, $payoutTogether = false, $fromCallCenter = false)
    {
        // Transaciona dados com antifraude
        if ($useAntiFraud && $this->getGateway($fromCallCenter)->hasAntifraud()) {

            // Transação com os dados do cartão
            $creditCardTransaction = $this->transact($order, $card, $referenceAppend . "_AF", $useAntiFraud, true, $payoutTogether, $fromCallCenter);

            if ($creditCardTransaction->getStatus()) {
                return $creditCardTransaction;
            }

            // Retry Transaction By Instant Buy Key
            if (!$this->cardIsIstantBuyKey($card) && !$creditCardTransaction->getStatus() && $creditCardTransaction->getPaymentToken()) {

                $instantBuyKeyTransaction = $this->transact($order, $creditCardTransaction->getPaymentToken(), "_AF_RETRY", $useAntiFraud, true, $payoutTogether);

                if( $instantBuyKeyTransaction->getStatus() ) {
                    return $instantBuyKeyTransaction;
                }
            }
        }

        // Transação com os dados do cartão SEM ANTIFRAUDE
        $creditCardTransaction = $this->transact($order, $card, $referenceAppend, false, true, $payoutTogether, $fromCallCenter);

        // Retry Transaction By Instant Buy Key
        if (!$this->cardIsIstantBuyKey($card) && !$creditCardTransaction->getStatus() && $creditCardTransaction->getPaymentToken()) {

            $instantBuyKeyTransaction = $this->transact($order, $creditCardTransaction->getPaymentToken(), "_RETRY", false, true, $payoutTogether, $fromCallCenter);

            if( $instantBuyKeyTransaction->getStatus() ) {
                return $instantBuyKeyTransaction;
            }
        }

        // Transação de retentativa com desconto provido pelo pacote
        if( $order->origin != 'system' && $creditCardTransaction->getPaymentToken() && $order->bundles && ($bundle = $order->bundles->first()) ) {
            if ( $bundle->retry_discount_1 > 0 OR $bundle->retry_discount_2 > 0 ) {
                $discountTransaction = $this->transactWithDiscount($order, $bundle, $creditCardTransaction->getPaymentToken(), $fromCallCenter);

                if ($discountTransaction->getStatus()) {
                    return $discountTransaction;
                }
            }
        }

        return $creditCardTransaction;
    }

    /**
     * @param Order $order
     * @param $creditCardData
     * @param string $appendReference
     * @param bool $useAntiFraud
     * @param bool $capture
     * @param bool $payoutTogether
     * @param bool $fromCallCenter
     * @return PaymentResponse
     */
    private function transact(Order $order, $creditCardData, $appendReference = "", $useAntiFraud = true, $capture = true, $payoutTogether = false, $fromCallCenter = false)
    {
        $creditCard = $this->getGateway($fromCallCenter);

        $cart       = $order->cartItems();
        $total      = $order->sub_total;
        $freight    = $order->freight_value;

        if ($payoutTogether && ($upsellOrder = $order->upsellOrder)) {
            if($upsellOrder->isPaid() && $upsellOrder->isCreditCard()){
                $total   += $upsellOrder->sub_total;
                $freight += $upsellOrder->freight_value;
                $cart     = $cart->merge($upsellOrder->cartItems());
            }
        }

        $transaction = $creditCard
            ->setCustomer($order->customer)
            ->setTotal($this->getValueInCents($total))
            ->setFreightValue($this->getValueInCents($freight))
            ->setInstallments($order->installments)
            ->setCreditCard($creditCardData)
            ->setIdentifier($order->id . $appendReference)
            ->setCartItems($cart)
            ->useAntifraud($useAntiFraud);

        if($capture){
            $transaction = $transaction->makeAuthAndCapture();
        }else{
            $transaction = $transaction->makeAuth();
        }

        $this->registerTransaction($transaction, $order, $transaction->getIdentifier());

        return $transaction;
    }

    /**
     * @param PaymentResponse $transaction
     * @param \App\Entities\Order $order
     * @param $reference
     * @return \App\Entities\Transaction
     */
    private function registerTransaction(PaymentResponse $transaction, $order, $reference)
    {
        return $this->transactionRepository->create([
            'order_id'      => $order->id,
            'type'          => TransactionType::CARTAO,
            'pay_reference' => $reference,
            'request_json'  => $transaction->getTransactionRequest(),
            'response_json' => $transaction->getTransactionResponse()
        ]);
    }

    /**
     * @param $value
     * @return string
     */
    private function getValueInCents($value)
    {
        return number_format($value, 2, '', '');
    }

    /**
     * Check id
     * @param $card
     * @return bool
     */
    private function cardIsIstantBuyKey($card)
    {
        return isset($card["instant_buy_key"]) && !empty($card["instant_buy_key"]);
    }

    /**
     * @param Order $order
     * @param Bundle $bundle
     * @param $card
     * @param bool $fromCallCenter
     * @return PaymentResponse
     */
    private function transactWithDiscount(Order $order, Bundle $bundle, $card, $fromCallCenter = false)
    {
        foreach(["retry_discount_1", "retry_discount_2"] as $discount) {
            if ($bundle->$discount > 0) {

                // Order
                $discountOrder = clone $order;
                $discountOrder->discount = $bundle->$discount;

                $description = $discount == "retry_discount_1"? "_DSCT_1" : "_DSCT_2";
                $transaction = $this->transact($discountOrder, $card, $description, $fromCallCenter);

                if ($transaction->getStatus()) {
                    $discountOrder->save();
                    return $transaction;
                }
            }
        }

        return new PaymentResponse(false, null, null, null, null, null);
    }

    /**
     * @param bool $fromCallCenter
     * @return CreditCard
     */
    private function getGateway($fromCallCenter = false)
    {
        return app( $this->getGatewayName($fromCallCenter), [$this->getPaymentSetting($fromCallCenter)] );
    }

    /**
     * @param bool $fromCallCenter
     * @return CreditCard
     */
    private function getGatewayName($fromCallCenter = false)
    {
        $paymentSetting = $this->getPaymentSetting($fromCallCenter);
        return config( 'payment.gateways.CreditCard.' . $paymentSetting->creditcard_gateway);
    }

    /**
     * @param bool $fromCallCenter
     * @return PaymentSetting
     */
    private function getPaymentSetting($fromCallCenter = false)
    {
        return $fromCallCenter ? $this->callCenterPaymentSettings : $this->paymentSettings;
    }

}
