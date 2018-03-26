<?php

namespace App\Http\Controllers\Site;

use App\Repositories\Additional\AdditionalRepository;
use Illuminate\Http\Request;
use App\Entities\Bundle;
use App\Entities\Customer;
use App\Entities\Order;

class CheckoutController extends BaseController
{
    /**
     * Display checkout page
     */
    public function index(Request $request)
    {
        $query = Bundle::onlyDefault();
        $bundles  = $query->where('bundle_group_id', $this->settings->getSite()->bundle_group_id)
                          ->get()
                          ->sortByDesc('price');

        $customer = Customer::find( app('customer_id') );
        $selectedBundle = $request->get('bundle_id')
                            ? $bundles->where( 'id', $request->get('bundle_id'), false )->first()
                            : $bundles->first();

        if( $this->settings->isRemarketing() ) {
            $payments = explode(",", $this->settings->getPaymentSettings()->retry_payments);
        } else {
            $payments = explode(",", $this->settings->getPaymentSettings()->payments);
        }

        $acquirerFlags = $this->getAcquirerFlags();

        return view('checkout::pages.index', compact('customer', 'bundles', 'selectedBundle', 'payments', 'acquirerFlags'));
    }

    /**
     * Display Promo Exit Page
     */
    public function promoExit(Request $request)
    {
        $customer = Customer::find( app('customer_id') );
        $bundles  = Bundle::onlyPromotional()
                            ->where('bundle_group_id', $this->settings->getSite()->bundle_group_id)
                            ->get()
                            ->sortByDesc('price');

        $selectedBundle = $request->get('bundle_id')
                            ? $bundles->where( 'id', $request->get('bundle_id'), false )->first()
                            : $bundles->first();

        $payments = explode(",", $this->settings->getPaymentSettings()->retry_payments);
        $acquirerFlags = $this->getAcquirerFlags();

        $extraFields = [
            "origin"    => "promoexit",
            "redirect"  => "checkout::checkout.exit"
        ];

        return view('checkout::pages.promoexit', compact('customer', 'bundles', 'selectedBundle', 'payments', 'acquirerFlags', 'extraFields'));
    }

    /**
     * Display Remarketing Page
     */
    public function remarketing(Request $request)
    {
        $customer = Customer::find( app('customer_id') );
        $bundles  = Bundle::onlyDefault()
                                ->where('bundle_group_id', $this->settings->getSite()->bundle_group_id)
                                ->get()
                                ->sortByDesc('price');

        $selectedBundle = $request->get('bundle_id')
            ? $bundles->where( 'id', $request->get('bundle_id'), false )->first()
            : $bundles->first();

        $payments = explode(",", $this->settings->getPaymentSettings()->retry_payments);
        $acquirerFlags = $this->getAcquirerFlags();

        $extraFields = [
            "origin"    => "promoexit",
            "redirect"  => "checkout::checkout.exit"
        ];

        return view('checkout::pages.remarketing', compact('customer', 'bundles', 'selectedBundle', 'payments', 'acquirerFlags', 'extraFields'));
    }

    /**
     * Display Upsell Page
     */
    public function upSell()
    {
        $order  = Order::with('bundles.products', 'bundles.upsell.products')->find( session("order_id") );
        $bundle = $order->bundles()->first();
        $upsellBundle = $bundle->upsell->first();

        return view('checkout::pages.upsell', compact('order', 'bundle', 'upsellBundle'));
    }

    /**
     * Display Additional Page
     * @param AdditionalRepository $additionalRepository
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function additional(AdditionalRepository $additionalRepository)
    {
        $order      = Order::with('bundles.products')->find( session("order_id") );
        $bundle     = $order->bundles()->first();
        $additional = $additionalRepository->getNextAdditional( $order );

        return view('checkout::pages.additional', compact('order', 'additional', 'bundle'));
    }

    /**
     * Display Retry Page
     */
    public function retry(Request $request)
    {
        $query = Bundle::onlyDefault();
        $bundles  = $query->where('bundle_group_id', $this->settings->getSite()->bundle_group_id)
                          ->get()
                          ->sortByDesc('price');

        $order    = Order::with(["bundles", "customer"])->find( session("order_id") );
        $customer = $order->customer;
        $payments = explode(",", $this->settings->getPaymentSettings()->retry_payments);
        $selectedBundle = $request->get('bundle_id')
                                  ? $bundles->where( 'id', $request->get('bundle_id'), false )->first()
                                  : $bundles->first();

        $acquirerFlags = $this->getAcquirerFlags();

        $page = 'retry';
        if($this->settings->getSite()->auto_refund){
            $page = 'outofstock';
        }

        return view('checkout::pages.'. $page ,
            compact('order', 'customer', 'bundles', 'payments', 'selectedBundle', 'acquirerFlags')
        );
    }

    public function rebuy(Request $request)
    {
        if ($request->has("email") && ($customer = Customer::whereEmail($request->get("email"))->first())) {
            if ($customer) {
                session()->put("customer_id", $customer->id);
            }
        }

        return redirect()->route("checkout::checkout.exit", $request->except("email"));
    }

    private function getAcquirerFlags()
    {
        $paymentSettings = $this->settings->getPaymentSettings();

        $acquirers = explode(",", $paymentSettings->credit_card_acquirers);

        $flags = [];

        foreach($acquirers as $acquirer) {
            if ($acquirer && config("payment.flags.".trim($acquirer))) {
                $flags = array_unique(array_merge(
                    $flags,
                    config("payment.flags.".trim($acquirer))
                ));
            }
        }

        return $flags;
    }
}
