<?php

namespace App\Services;

use App\Domain\OrderStatus;
use App\Domain\TransactionType;
use App\Entities\Bundle;
use App\Entities\Customer;
use App\Entities\Order;
use App\Entities\PaymentSetting;
use App\Entities\Site;
use App\Entities\Status;
use App\Repositories\Orders\OrderRepository;
use App\Repositories\Transaction\TransactionRepository;
use App\Services\Encoder\JsonEncoder;
use App\Services\Order\CreateOrderService;
use App\Support\SiteSettings;
use Netshell\Paypal\Facades\Paypal;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\ShippingAddress;
use PayPal\Api\Transaction;
use PayPal\Rest\ApiContext;

class PayPalTransaction
{
    /**
     * @var CreateOrderService
     */
    private $createOrderService;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var TransactionRepository
     */
    private $transactionRepository;

    /**
     * @var SiteSettings
     */
    private $siteSettings;

    /**
     * @var PaymentSetting
     */
    private $paymentSettings;

    /**
     * PagseguroTransaction constructor.
     * @param SiteSettings $siteSettings
     * @param CreateOrderService $createOrderService
     * @param OrderRepository $orderRepository
     * @param TransactionRepository $transactionRepository
     */
    public function __construct(SiteSettings $siteSettings,
                                CreateOrderService $createOrderService,
                                OrderRepository $orderRepository,
                                TransactionRepository $transactionRepository)
    {
        $this->createOrderService = $createOrderService;
        $this->orderRepository = $orderRepository;
        $this->transactionRepository = $transactionRepository;
        $this->siteSettings = $siteSettings;
        $this->paymentSettings = $siteSettings->getPaymentSettings();
    }

    /**
     * Use this to Specific Payment Settings
     * @param Site $site
     * @return $this
     */
    public function setPaymentsFromSite(Site $site)
    {
        $this->paymentSettings          = $site->paymentSetting;
        return $this;
    }

    /**
     * Create Pagseguro transaction from bundle
     * @param Bundle $bundle
     * @param Customer $customer
     * @param $origin
     * @return string Redirection url
     */
    public function createFromBundle(Bundle $bundle, Customer $customer, $origin)
    {
        if ($orderId = session()->get("order_id")) {
            $order = $this->orderRepository->find($orderId);
            $order = $this->createOrderService->update($order, $bundle->price, $bundle->freight_value, Status::PENDING, [$bundle], 'PayPal', 'PayPal', 1, $origin);
        } else {
            $order = $this->createOrderService->create($customer, $bundle->price, $bundle->freight_value, Status::PENDING, [$bundle], 'PayPal', 'PayPal', 1, $origin);
        }

        session()->put("order_id", $order->id);

        return $this->createFromOrder($order, $customer);
    }

    /**
     * Create pagseguro transaction from order
     * @param Order $order
     * @param Customer $customer
     * @return string Redirection url
     */
    public function createFromOrder(Order $order, Customer $customer)
    {
        $order = $this->orderRepository->update([
            'payment_type_collection'   => 'PayPal',
            'payment_type'              => 'PayPal'
        ], $order->id);

        return $this->makeTransaction($order, $customer);
    }

    /**
     * @param $paymentId
     * @param $payerId
     * @return Payment
     */
    public function confirmPayment($paymentId, $payerId)
    {
        $credentials = $this->getCredentials();
        $payment = PayPal::getById($paymentId, $credentials);


        if (isset($payment->transactions[0]->invoice_number)) {

            $payReference = $payment->transactions[0]->invoice_number;

            if($order = $this->findOrder($payReference)) {

                if ($payment->getState() != "approved") {
                    $paymentExecution = PayPal::PaymentExecution();
                    $paymentExecution->setPayerId($payerId);
                    $payment = $payment->execute($paymentExecution, $credentials);
                    $this->registerTransaction($order, $payReference, $paymentExecution, $payment);
                }

                if ($payment->getState() == "approved") {
                    $order->update(['status' => OrderStatus::APPROVED]);
                }
            }
        }

        return $payment;
    }

    /**
     * Make Pagseguro Transaction
     * @param $order
     * @param $customer
     * @return string redirection url
     */
    private function makeTransaction(Order $order, Customer $customer)
    {
        $payer          = $this->getPayer();
        $transaction    = $this->getTransaction($order);
        $redirectUrls   = $this->getRedirectUrls();
        $payment        = $this->getPayment();

        $payment->setPayer($payer);
        $payment->setRedirectUrls($redirectUrls);
        $payment->setTransactions([$transaction]);

        $response = $payment->create($this->getCredentials());

        $this->registerTransaction($order, $transaction->invoice_number, $payment, $response);

        return $response->links[1]->href;
    }

    /**
     * @return Payer
     */
    private function getPayer()
    {
        $payer = Paypal::Payer();
        $payer->setPaymentMethod('paypal');
        return $payer;
    }

    /**
     * @param Order $order
     * @return Transaction
     */
    private function getTransaction(Order $order)
    {
        $details = PayPal::Details();
        $details->setShipping($order->freight_value)
                ->setSubtotal($order->total);

        $cart  = PayPal::ItemList();
        $cart->setItems($this->getCartItems($order));
        $cart->setShippingAddress($this->getShippingAddress($order->customer));

        $amount = PayPal::Amount();
        $amount->setCurrency('BRL');
        $amount->setTotal($order->sub_total);
        $amount->setDetails($details);

        $transaction = PayPal::Transaction();
        $transaction->setAmount($amount);
        $transaction->setItemList($cart);
        $transaction->setDescription($this->paymentSettings->paypal_description);
        $transaction->setInvoiceNumber("paypal_" . $order->id ."_". substr(md5(microtime()), 0, 5));

        return $transaction;
    }

    /**
     * @return RedirectUrls
     */
    private function getRedirectUrls()
    {
        $redirectUrls = PayPal::RedirectUrls();
        $redirectUrls->setReturnUrl(route('checkout::payment.paypal.confirm'));
        $redirectUrls->setCancelUrl(route('checkout::checkout.retry'));

        return $redirectUrls;
    }

    /**
     * @return Payment
     */
    private function getPayment()
    {
        $payment = PayPal::Payment();
        $payment->setIntent('sale');

        return $payment;
    }

    /**
     * @return ApiContext
     */
    private function getCredentials()
    {
        $context = PayPal::ApiContext(
            $this->paymentSettings->paypal_client_id,
            $this->paymentSettings->paypal_secret_key
        );

        $context->setConfig([
            'mode'                      => $this->paymentSettings->paypal_environment,
            'service.EndPoint'          => config("paypal.{$this->paymentSettings->paypal_environment}"),
            'http.ConnectionTimeOut'    => 60,
            'log.LogEnabled'            => true,
            'log.FileName'              => storage_path('logs/paypal.log'),
            'log.LogLevel'              => 'FINE'
        ]);

        return $context;
    }

    /**
     * @param Order $order
     * @return array
     */
    private function getCartItems(Order $order)
    {
        $items = [];

        foreach($order->cartItems() as $item){
            $payPayItem = PayPal::Item();
            $payPayItem->setName($item->description)
                        ->setCurrency('BRL')
                        ->setQuantity($item->qty)
                        ->setPrice($item->price);

            $items[] = $payPayItem;
        }

        return $items;
    }

    /**
     * @param Customer $customer
     * @return ShippingAddress
     */
    private function getShippingAddress(Customer $customer)
    {
        $shippingAddress = PayPal::ShippingAddress();
        $shippingAddress->setLine1($customer->address_street . ", ". $customer->address_street_number)
            ->setLine2($customer->address_street_complement . ", ". $customer->address_street_district)
            ->setCity($customer->address_city)
            ->setState($customer->address_state)
            ->setPostalCode($customer->postcode)
            ->setCountryCode("BR")
            ->setPhone($customer->telephone)
            ->setRecipientName($customer->fullName());

        return $shippingAddress;
    }

    /**
     * @param $order
     * @param $reference
     * @param $request
     * @param $response
     * @return mixed
     */
    private function registerTransaction($order, $reference, $request, $response)
    {
        return $this->transactionRepository->create([
            'order_id'      => $order->id,
            'type'          => TransactionType::PAYPAL,
            'pay_reference' => $reference,
            'response_json' => $response->toJSON(),
            'request_json' => $request->toJSON(),
        ]);
    }

    /**
     * @param string $pay_reference
     * @return Order
     */
    private function findOrder($pay_reference)
    {
        $transaction = $this->transactionRepository->findByField("pay_reference", $pay_reference)->first();

        if($transaction && $transaction->order){
            return $transaction->order;
        }

        return null;
    }

}