<?php

namespace App\Http\Controllers\Admin;

use App\Domain\OrderStatus;
use App\Http\Requests\Admin\BoletoRequest;
use App\Http\Requests\Admin\CreditCardRequest;
use App\Http\Requests\Admin\PagSeguroRequest;
use App\Repositories\Bundle\BundleRepository;
use App\Repositories\Customers\CustomerRepository;
use App\Repositories\Orders\OrderRepository;
use App\Services\PagseguroTransaction;
use Carbon\Carbon;
use App\Services\BoletoTransaction;
use App\Services\CreditCardTransaction;
use App\Support\SiteSettings;

/**
 * Class CheckoutController
 * @package App\Http\Controllers
 */
class CheckoutController extends Controller
{
    /**
     * @var BundleRepository
     */
    private $bundleRepository;

    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * CheckoutController constructor.
     * @param SiteSettings $settings
     * @param BundleRepository $bundleRepository
     * @param CustomerRepository $customerRepository
     * @param OrderRepository $orderRepository
     */
    public function __construct(SiteSettings $settings,
                                BundleRepository $bundleRepository,
                                CustomerRepository $customerRepository,
                                OrderRepository $orderRepository)
    {
        parent::__construct( $settings );
        $this->bundleRepository = $bundleRepository;
        $this->customerRepository = $customerRepository;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Process CreditCard Payment
     *
     * @param CreditCardRequest $request
     * @param CreditCardTransaction $cardTransaction
     * @return \Illuminate\Http\Response
     */
    public function paymentCreditCard(CreditCardRequest $request,
                                      CreditCardTransaction $cardTransaction)
    {
        $card = $request->input();

        $order = $this->orderRepository->find($request->input('order_id'));

        $customer = $request->input('cpfcartao') ? $this->customerRepository->update([
                'document_number' => $request->input('cpfcartao')
            ], $order->customer->id) : $order->customer;

        if ($card && $customer) {
            $useAF = false;
            $payment = $cardTransaction
                        ->setPaymentsFromSite($customer->site)
                        ->payOrder(
                            $card, $order, $request->input('installments'), "system", $useAF, $request->input("payout_together"), true
                        );
            return response()->json($payment);
        }

        return response()->json(['status'=>false, 'message'=>'Ops, algo parece errado, preecha todos os campos e tente novamente']);
    }

    /**
     * Process Boleto Payment
     *
     * @param BoletoRequest $request
     * @param BoletoTransaction $boletoTransaction
     * @return \Illuminate\Http\Response
     */
    public function paymentBoleto(BoletoRequest $request, BoletoTransaction $boletoTransaction)
    {
        $customer = $this->customerRepository->update([
            'document_number' => $request->input('cpfboleto')
        ], $request->input('customer_id'));

        $order = $this->orderRepository->find( $request->input('order_id') );

        $dueDate = new Carbon( $request->input('due_date') );

        $payment = $boletoTransaction->setPaymentsFromSite($customer->site)
                                     ->create($customer, $order, $dueDate, true);

        return response()->json($payment);
    }

    /**
     * Process PagSeguro Payment
     *
     * @param PagSeguroRequest $request
     * @param PagseguroTransaction $pagseguroTransaction
     * @return \Illuminate\Http\Response
     */
    public function paymentPagSeguro(PagSeguroRequest $request, PagseguroTransaction $pagseguroTransaction)
    {
        $order = $this->orderRepository->find( $request->input('order_id') );
        $customer = $order->customer;

        $response = $pagseguroTransaction->setPaymentsFromSite($customer->site)->addTransaction(
            $order,
            $request->get("transaction_key")
        );

        if ($response->getStatus()) {
            $this->orderRepository->update([
                'payment_type_collection'  => config("payment.types.Pagseguro"),
                'payment_type'  => config("payment.types.Pagseguro"),
                'status'        => OrderStatus::APPROVED,
                'installments'  => 1,
                'origin'        => 'system',
                'user_id'       => $order->user_id ?: auth()->user()->id
            ], $order->id);
        }

        return response()->json( $response );
    }

}
