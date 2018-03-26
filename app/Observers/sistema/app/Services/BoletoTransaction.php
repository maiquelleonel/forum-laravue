<?php

namespace App\Services;

use App\Domain\OrderStatus;
use App\Entities\Bundle;
use App\Entities\Customer;
use App\Entities\Order;
use App\Entities\PaymentSetting;
use App\Entities\Site;
use App\Entities\Status;
use App\Domain\TransactionType;
use App\Repositories\Customers\CustomerRepository;
use App\Repositories\Orders\OrderRepository;
use App\Repositories\Transaction\TransactionRepository;
use App\Services\Gateways\Contracts\Boleto;
use App\Services\Gateways\PaymentResponse;
use App\Services\Order\CreateOrderService;
use App\Support\SiteSettings;
use Carbon\Carbon;

class BoletoTransaction
{
    /**
     * @var CreateOrderService
     */
    private $createOrderService;
    /**
     * @var CustomerRepository
     */
    private $customerRepository;
    /**
     * @var TransactionRepository
     */
    private $transactionRepository;
    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var PaymentSetting
     */
    private $paymentSettings;

    /**
     * @var PaymentSetting
     */
    private $callCenterPaymentSettings;

    /**
     * BoletoTransaction constructor.
     * @param SiteSettings $siteSettings
     * @param CreateOrderService $createOrderService
     * @param CustomerRepository $customerRepository
     * @param OrderRepository $orderRepository
     * @param TransactionRepository $transactionRepository
     * @internal param Boleto $boleto
     */
    public function __construct(SiteSettings $siteSettings,
                                CreateOrderService $createOrderService,
                                CustomerRepository $customerRepository,
                                OrderRepository $orderRepository,
                                TransactionRepository $transactionRepository)
    {
        $this->paymentSettings = $siteSettings->getPaymentSettings();
        $this->callCenterPaymentSettings= $siteSettings->getCallCenterPaymentSettings();
        $this->createOrderService = $createOrderService;
        $this->customerRepository = $customerRepository;
        $this->transactionRepository = $transactionRepository;
        $this->orderRepository = $orderRepository;
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
     * @param Customer $customer
     * @param Order $order
     * @param Carbon $dueDate
     * @param bool $fromCallCenter
     * @return PaymentResponse
     */
    public function create(Customer $customer, Order $order, Carbon $dueDate = null, $fromCallCenter = false)
    {
        $transaction = $this->getGateway($fromCallCenter)
                         ->setCustomer($customer)
                         ->setDueDate($dueDate)
                         ->setValue($this->getOrderValue($order))
                         ->make();

        $this->registerTransaction($transaction, $order);

        if ($transaction->getStatus() ) {
            $this->orderRepository->update([
                'payment_type'              => $this->getGatewayName($fromCallCenter),
                'payment_type_collection'   => "Boleto",
                'status'        => OrderStatus::PENDING,
                'installments'  => 1,
                'origin'        => $fromCallCenter ? 'system' : $order->origin,
                'user_id'       => $order->user_id ?: (auth()->user() ? auth()->user()->id : null)
            ], $order->id);
        }

        return $transaction;
    }

    /**
     * @param Customer $customer
     * @param Bundle $bundle
     * @param Carbon|null $dueDate
     * @param null $origin
     * @param bool $fromCallCenter
     * @return PaymentResponse
     */
    public function createFromBundle(Customer $customer, Bundle $bundle, Carbon $dueDate = null, $origin = null, $fromCallCenter = false)
    {
        if ($orderId = session()->get("order_id")) {
            $order = $this->orderRepository->find($orderId);
            $order = $this->createOrderService->update(
                $order, $bundle->price, $bundle->freight_value, Status::PENDING, [$bundle], 'Boleto', $this->getGatewayName($fromCallCenter), 1, $origin
            );
        } else {
            $order = $this->createOrderService->create(
                $customer, $bundle->price, $bundle->freight_value, Status::PENDING, [$bundle], 'Boleto', $this->getGatewayName($fromCallCenter), 1, $origin
            );
        }

        session()->put("order_id", $order->id);

        return $this->create($customer, $order, $dueDate, $fromCallCenter);
    }

    /**
     * @param PaymentResponse $transaction
     * @param $order
     * @return mixed
     */
    private function registerTransaction(PaymentResponse $transaction, $order)
    {
        return $this->transactionRepository->create([
            'order_id'      => $order->id,
            'type'          => TransactionType::BOLETO,
            'pay_reference' => $transaction->getIdentifier(),
            'request_json'  => $transaction->getTransactionRequest(),
            'response_json' => $transaction->getTransactionResponse()
        ]);
    }

    /**
     * Retorna o total da order com valor de frete (caso houver)
     *
     * @param $order
     * @return mixed
     */
    private function getOrderValue($order)
    {
        return $order->total + $order->freight_value - $order->discount;
    }

    /**
     * @param bool $fromCallCenter
     * @return Boleto
     */
    private function getGateway($fromCallCenter = false)
    {
        return app( $this->getGatewayName($fromCallCenter), [$this->getPaymentSetting($fromCallCenter)] );
    }

    /**
     * @param bool $fromCallCenter
     * @return Boleto
     */
    private function getGatewayName($fromCallCenter = false)
    {
        $paymentSetting = $this->getPaymentSetting($fromCallCenter);
        return config( 'payment.gateways.Boleto.' . $paymentSetting->billet_gateway);
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