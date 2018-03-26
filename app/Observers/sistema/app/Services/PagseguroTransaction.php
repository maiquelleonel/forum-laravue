<?php

namespace App\Services;

use App\Entities\Bundle;
use App\Entities\Customer;
use App\Entities\Order;
use App\Entities\PaymentSetting;
use App\Entities\Site;
use App\Entities\Status;
use App\Domain\TransactionType;
use App\Repositories\Orders\OrderRepository;
use App\Repositories\Transaction\TransactionRepository;
use App\Services\Encoder\JsonEncoder;
use App\Services\Gateways\PaymentResponse;
use App\Services\Order\CreateOrderService;
use App\Support\SiteSettings;
use laravel\pagseguro\Checkout\SimpleCheckout;
use laravel\pagseguro\Credentials\Credentials as PagSeguroCredentials;
use laravel\pagseguro\Facades\Checkout;
use laravel\pagseguro\Facades\Credentials;
use PHPSC\PagSeguro\Credentials as PhpScCredentials;
use PHPSC\PagSeguro\Environments\Production;
use PHPSC\PagSeguro\Environments\Sandbox;
use PHPSC\PagSeguro\Purchases\Transactions\Locator;

class PagseguroTransaction
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
            $order = $this->createOrderService->update($order, $bundle->price, $bundle->freight_value, Status::PENDING, [$bundle], 'Pagseguro', 'Pagseguro', 1, $origin);
        } else {
            $order = $this->createOrderService->create($customer, $bundle->price, $bundle->freight_value, Status::PENDING, [$bundle], 'Pagseguro', 'Pagseguro', 1, $origin);
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
            'payment_type_collection'   => 'Pagseguro',
            'payment_type'              => 'Pagseguro'
        ], $order->id);

        return $this->makeTransaction($order, $customer);
    }

    /**
     * Make Pagseguro Transaction
     * @param $order
     * @param $customer
     * @return string redirection url
     */
    private function makeTransaction(Order $order, Customer $customer)
    {
        $checkoutService = $this->getCheckoutService();

        $reference = $this->paymentSettings->pagseguro_prefix . $order->id . "_" . substr(md5(microtime()), 0, 5);

        $data = [
            "sender"        => $this->getCustomer($customer),
            "shipping"      => $this->getShipping($order, $customer),
            "items"         => $this->getItems($order),
            "reference"     => $reference
        ];

        if( app()->environment() == "local" ) {
            $data["redirectURL"] = "localhost";
            $data["notificationURL"] = "localhost";
        } else {
            $data["redirectURL"] = route("checkout::success.pagSeguro");
            $data["notificationURL"] = route("notification::pagseguro");
        }

        $checkout = $checkoutService->createFromArray($data);
        $response = $checkout->send($this->getCredentials());

        if($response){
            $this->registerTransaction($order, $reference, $data, $response);
            return $response->getLink();
        }

        return null;
    }

    /**
     * @return \laravel\pagseguro\Facades\Checkout
     */
    private function getCheckoutService(){
        return new Checkout();
    }

    /**
     * @param Customer $customer
     * @return array
     */
    private function getCustomer(Customer $customer)
    {
        return [
            "email" => $customer->email,
            "name"  => $customer->firstname . " " . $customer->lastname,
            "phone" => $this->getPhone($customer)
        ];
    }

    private function getShipping(Order $order, Customer $customer)
    {
        if ($order->freight_value <= 0) {
            return "";
        }

        return [
            'address' => [
                'postalCode' => $customer->postcode,
                'street' => $customer->address_street,
                'number' => $customer->address_street_number,
                'complement' => $customer->address_street_complement,
                'district' => $customer->address_street_district,
                'city' => $customer->address_city,
                'state' => $customer->address_state,
                'country' => 'BRA',
            ],
            'type' => 1,
            'cost' => $order->freight_value,
        ];
    }

    /**
     * @param Order $order
     * @return array
     */
    private function getItems(Order $order)
    {
        $items = [];

        $item = function($id, $name, $price, $qty){
            return [
                'id'            => $id,
                'description'   => $name,
                'quantity'      => $qty,
                'amount'        => $price
            ];
        };

        foreach($order->bundles as $bundle) {
            $items[] = $item(
                $bundle->id, $bundle->name, $bundle->price, $bundle->pivot->qty
            );
        }

        foreach($order->products as $product) {
            $items[] = $item(
                $product->id, $product->name, $product->pivot->price, $product->pivot->qty
            );
        }

        return $items;
    }

    /**
     * Get Phone Number
     * @param Customer $customer
     * @return string
     */
    private function getPhone(Customer $customer)
    {
        $replaces = [ ' ', '(', ')', '-', '_'];

        return str_ireplace($replaces, "", $customer->telephone);
    }

    /**
     * @return PagSeguroCredentials
     */
    private function getCredentials()
    {
        config([
            "laravelpagseguro.use-sandbox" => $this->paymentSettings->pagseguro_environment != 'production',
        ]);
        return new PagSeguroCredentials(
            $this->paymentSettings->pagseguro_token,
            $this->paymentSettings->pagseguro_email
        );
    }

    /**
     * @param Order $order
     * @param $transactionKey
     * @return PaymentResponse
     */
    public function addTransaction(Order $order, $transactionKey)
    {
        try {
            $response = $this->getTransaction($transactionKey);
            $amount = $response->getPayment()->getGrossAmount();
            $total = $order->total + $order->freight_value - $order->discount;

            $transactions = $this->transactionRepository
                    ->findWhere([
                        ["request_json", "LIKE", "%{$transactionKey}%"],
                        ["order_id", "!=", $order->id]
                    ], [\DB::raw("DISTINCT order_id")]);

            if ($transactions->count() > 0) {
                return new PaymentResponse(
                    false, 200, "Este código já foi usado no pedido " . $transactions->lists("order_id")->implode(","), [], "", ""
                );
            }

            if ((int) $total === (int) $amount) {
                $this->registerTransaction($order, $transactionKey, (object)["key"=>$transactionKey], $response);
                return new PaymentResponse(true, 200, "Transação validada com sucesso!", [], "", "");
            } else {
                $pagSeguro = monetary_format($amount);
                $system = monetary_format($total);
                return new PaymentResponse(
                    false, 200, "Valor do pedido não confere, Pagseguro: $pagSeguro, Sistema: $system", [], "", ""
                );
            }
        } catch (\Exception $e) {
            return new PaymentResponse(false, $e->getCode(), "Código de transação inválido!", [], "", "");
        }
    }

    public function findTransaction($transactionKey)
    {
        try {
            $response = $this->getTransaction( $transactionKey );
            return new PaymentResponse($response->isPaid(), 200, "Transação validada com sucesso!", [], "", $response);
        } catch (\Exception $e) {
            return new PaymentResponse(false, $e->getCode(), "Código de transação inválido!", [], "", "");
        }
    }

    private function getTransaction($transactionKey)
    {
        $environment = $this->paymentSettings->pagseguro_environment == 'production'
            ? new Production()
            : new Sandbox();

        $credentials = new PhpScCredentials(
            $this->paymentSettings->pagseguro_email,
            $this->paymentSettings->pagseguro_token,
            $environment
        );

        $locator = new Locator( $credentials );
        return $locator->getByCode( $transactionKey );
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
        $encoder = new JsonEncoder;

        if (is_array($request)) {
            $request = (object)$request;
        }

        return $this->transactionRepository->create([
            'order_id'      => $order->id,
            'type'          => TransactionType::PAGSEGURO,
            'pay_reference' => $reference,
            'response_json' => $encoder->encode($response),
            'request_json' => $encoder->encode($request),
        ]);
    }

}