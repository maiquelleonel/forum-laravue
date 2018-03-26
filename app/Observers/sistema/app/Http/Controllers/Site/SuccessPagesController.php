<?php

namespace App\Http\Controllers\Site;

use App\Repositories\Customers\CustomerRepository;
use App\Repositories\Orders\OrderRepository;
use App\Support\SiteSettings;
use App\Domain\BundleCategory;

class SuccessPagesController extends BaseController
{
    /**
     * @var OrderRepository
     */
    private $orderRepository;
    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * SuccessPagesController constructor.
     * @param SiteSettings $siteSettings
     * @param OrderRepository $orderRepository
     * @param CustomerRepository $customerRepository
     */
    public function __construct(SiteSettings $siteSettings, OrderRepository $orderRepository, CustomerRepository $customerRepository)
    {
        parent::__construct($siteSettings);
        $this->orderRepository = $orderRepository;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Default Success Page
     * @return \Illuminate\Http\RedirectResponse
     */
    public function successPage()
    {
        $order = session( "order_id" );

        if($order = $this->orderRepository->find($order)) {

            if ($order->payment_type_collection == 'Boleto') {

                return redirect()->route('checkout::success.boleto');

            } else if ($order->payment_type_collection == 'Pagseguro') {

                return redirect()->route('checkout::success.pagSeguro');

            } else if ($order->payment_type_collection == 'PayPal') {

                return redirect()->route('checkout::success.payPal');

            }
        }

        return redirect()->route("checkout::success.creditCard");
    }

    /**
     * Credit Card success page
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function creditCard()
    {
        $order       = $this->orderRepository
                           ->with(['customer', 'lastCreditCardTransaction', 'bundles', 'products'])
                           ->find( session( "order_id" ) );
        $customer    = $order->customer;
        $transaction = $order->validCreditCardTransaction;


        $bundle               = $order->bundles()->latest()->first();
        $is_upsell            = false;
        $refunded_transaction = false;
        $new_transaction      = false;
        if( (is_object($bundle) && $bundle->category == BundleCategory::UPSELL) && $order->lastRefundedCreditcardTransaction()){
            $refunded_transaction = $order->lastRefundedCreditcardTransaction()->getTransaction();
            $is_upsell            = true;
            $new_transaction      = $order->validCreditCardTransaction()->first()->getTransaction();
        }


        return view('thankyou::pages.credit-card', compact(
            'customer', 'order', 'transaction','is_upsell','refunded_transaction', 'new_transaction'
        ));
    }

    /**
     * Boleto Success Page
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function boleto()
    {
        $order      = $this->orderRepository
                           ->with(['customer', 'lastBoletoTransaction', 'bundles', 'products'])
                           ->find( session( "order_id" ) );
        $customer   = $order->customer;

        $transaction= $order->lastBoletoTransaction;

        return view('thankyou::pages.boleto', compact('customer', 'order', 'transaction'));
    }

    /**
     * Pagseguro Success Page
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function pagSeguro()
    {
        $order      = $this->orderRepository
                           ->with(['customer', 'transactions', 'bundles', 'products'])
                           ->find( session( "order_id" ) );
        $customer   = $order->customer;

        return view('thankyou::pages.pagseguro', compact('customer', 'order'));
    }

    /**
     * PayPal Success Page
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function payPal()
    {
        $order      = $this->orderRepository
                            ->with(['customer', 'transactions', 'bundles', 'products'])
                            ->find( session( "order_id" ) );
        $customer   = $order->customer;

        return view('thankyou::pages.paypal', compact('customer', 'order'));
    }

}
