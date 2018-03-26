<?php

namespace App\Http\Controllers\Site;

use App\Domain\TransactionType;
use App\Http\Requests\Site\BoletoRequest;
use App\Repositories\Additional\AdditionalRepository;
use App\Services\PayPalTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Entities\Bundle;
use App\Repositories\Bundle\BundleRepository;
use App\Repositories\Customers\CustomerRepository;
use App\Repositories\Orders\OrderRepository;
use App\Services\BoletoTransaction;
use App\Services\CreditCardTransaction;
use App\Http\Requests\Site\CreditCardRequest;
use App\Services\PagseguroTransaction;
use App\Support\SiteSettings;


class PaymentController extends BaseController
{
    /**
     * @var CustomerRepository
     */
    private $customerRepository;
    /**
     * @var BundleRepository
     */
    private $bundleRepository;
    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * PaymentController constructor.
     * @param SiteSettings $siteSettings
     * @param CustomerRepository $customerRepository
     * @param BundleRepository $bundleRepository
     * @param OrderRepository $orderRepository
     */
    public function __construct(SiteSettings $siteSettings,
                                CustomerRepository $customerRepository,
                                BundleRepository $bundleRepository,
                                OrderRepository $orderRepository)
    {
        parent::__construct($siteSettings);
        $this->customerRepository = $customerRepository;
        $this->bundleRepository = $bundleRepository;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Payment CreditCard
     * @param CreditCardRequest $request
     * @param CreditCardTransaction $cardTransaction
     * @return $this|RedirectResponse
     */
    public function creditCard(CreditCardRequest $request, CreditCardTransaction $cardTransaction)
    {
        $card = $request->input();

        $bundle = $this->getBundle( $request->get("bundle_id") );

        $customer = app('customer_id') ? $this->customerRepository->update([
            'document_number' => $request->input('cpfcartao')
        ], app('customer_id')) : null;

        if ($card && $bundle && $customer) {

            $payment = $cardTransaction->create($card, $bundle, $customer, $this->getInstallments($bundle, $request), $request->input('origin'));

            if ( $payment->getStatus() ) {
                return redirect()->route('checkout::checkout.upsell');
            }
            $error = $payment->getMessage();
        }

        $error = isset($error) ? $error : 'Confira todos seus dados e tente novamente';

        $redirect = $this->getRedirect( $request->input('redirect'), 'checkout::checkout.retry' );

        return redirect()->route($redirect, $request->only('bundle_id'))
                         ->with('error', $error)
                         ->withInput();
    }

    /**
     * UpSell from order
     * @param Request $request
     * @param CreditCardTransaction $cardTransaction
     * @return RedirectResponse
     */
    public function creditCardUpSell(Request $request, CreditCardTransaction $cardTransaction)
    {
        if ($request->get("turbinado")) {
            $order = $this->orderRepository->find( session("order_id") );
            $payment = $cardTransaction->upSellFromOrder($order);
        }

        return redirect()->route('checkout::checkout.additional');
    }

    /**
     * Additional from order
     * @param Request $request
     * @param CreditCardTransaction $cardTransaction
     * @param AdditionalRepository $additionalRepository
     * @return RedirectResponse
     */
    public function creditCardAdditional(Request $request,
                                         CreditCardTransaction $cardTransaction,
                                         AdditionalRepository $additionalRepository)
    {
        $order = $this->orderRepository->find( session("order_id") );

        if( $request->get("turbinado") && $request->get("additional_id") && $request->get("qty") >= 1 ) {
            $payment = $cardTransaction->additionalFromOrder(
                $order, $additionalRepository->getNextAdditional($order), $request->get("qty")
            );
        }

        session()->put("last_additional", $request->get("additional_id"));

        if ($additionalRepository->getNextAdditional($order)) {
            return redirect()->route("checkout::checkout.additional");
        }

        return redirect()->route('checkout::successPage');
    }

    /**
     * Payment Boleto
     * @param BoletoRequest $request
     * @param BoletoTransaction $boletoTransaction
     * @param BundleRepository $bundleRepository
     * @return $this|RedirectResponse
     */
    public function boleto(BoletoRequest $request,
                           BoletoTransaction $boletoTransaction,
                           BundleRepository $bundleRepository)
    {
        $customer = app('customer_id') ? $this->customerRepository->update([
            'document_number' => $request->get('cpfboleto')
        ], app('customer_id')) : null;

        $bundle = $bundleRepository->find($request->input('bundle_id', 1));

        $payment = $boletoTransaction->createFromBundle($customer, $bundle, null, $request->input('origin'));

        if($payment->getStatus()) {
            return redirect()->route('checkout::successPage');
        }

        $redirect = $this->getRedirect( $request->get('redirect'), 'checkout::checkout.retry' );

        return redirect()->route($redirect, $request->only('bundle_id'))
                         ->with('error', $payment->getMessage())
                         ->withInput();
    }

    /**
     * Payment PagSeguro
     * @param Request $request
     * @param PagseguroTransaction $pagSeguroTransaction
     * @param BundleRepository $bundleRepository
     * @return RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function pagSeguro(Request $request,
                              PagseguroTransaction $pagSeguroTransaction,
                              BundleRepository $bundleRepository)
    {
        $customer = app('customer_id') ? $this->customerRepository->find(app('customer_id')) : null;

        $bundle = $this->getBundle( $request->input('bundle_id') );

        try {
            $urlPayment = $pagSeguroTransaction->createFromBundle($bundle, $customer, $request->input('origin'));
            return redirect($urlPayment);
        } catch (\Exception $e) {
            session()->flash('error', 'Revise todas suas informações de contato e tente novamente.');
        }

        $redirect = $this->getRedirect( $request->get('redirect'), 'checkout::checkout.retry' );

        return redirect()->route($redirect)->withInput();
    }

    /**
     * @param Request $request
     * @param PayPalTransaction $payPalTransaction
     * @return $this|RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function paypal(Request $request, PayPalTransaction $payPalTransaction)
    {
        $customer = app('customer_id') ? $this->customerRepository->find(app('customer_id')) : null;

        $bundle = $this->getBundle( $request->input('bundle_id') );

        try {
            if($urlPayment = $payPalTransaction->createFromBundle($bundle, $customer, $request->input('origin'))){
                return redirect($urlPayment);
            };
        } catch (\Exception $e) {
            session()->flash('error', 'Revise todas suas informações de contato e tente novamente.');
        }

        $redirect = $this->getRedirect( $request->get('redirect'), 'checkout::checkout.retry' );

        return redirect()->route($redirect)->withInput();
    }

    public function confirmPayPal(Request $request, PayPalTransaction $payPalTransaction)
    {
        if ($request->has(["paymentId", "PayerID"])) {
            $payPalTransaction->confirmPayment($request->get("paymentId"), $request->get("PayerID"));
        }

        return redirect()->route('checkout::success.payPal');
    }

    /**
     * @param Bundle $bundle
     * @param Request $request
     * @return mixed
     */
    private function getInstallments($bundle, $request)
    {
        if ($installments = $request->get('installments')) {
            if ($installments >= 1 && $installments <=12) {
                return $installments;
            }
        }

        return $bundle->installments;
    }

    /**
     * @param $bundle_id
     * @return Bundle|null
     */
    private function getBundle($bundle_id)
    {
        if ($bundle_id) {
            try {
                return $this->bundleRepository->find($bundle_id);
            } catch (\Exception $e) {
            }
        }

        return null;
    }

    /**
     * @param $route
     * @param $default
     * @return $this
     */
    private function getRedirect($route, $default)
    {
        if ($route && \Route::has( $route )) {
            return $route;
        }

        return $default;
    }
}
