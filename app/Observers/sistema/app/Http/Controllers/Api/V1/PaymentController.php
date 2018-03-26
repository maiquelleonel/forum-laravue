<?php
/**
 * Created by PhpStorm.
 * User: dlima
 * Date: 1/19/18
 * Time: 12:55
 */

namespace App\Http\Controllers\Api\V1;

use App\Domain\OrderStatus;
use App\Entities\Order;
use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\V1\BoletoPaymentRequest;
use App\Http\Requests\Api\V1\CreditCardPaymentRequest;
use App\Http\Responses\ApiResponse;
use App\Repositories\Orders\OrderRepository;
use App\Services\BoletoTransaction;
use App\Services\CreditCardTransaction;
use App\Services\Customer\CreateCustomerService;
use App\Services\Order\CreateOrderService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PaymentController extends BaseController
{
    /**
     * @var CreateOrderService
     */
    private $orderService;
    /**
     * @var OrderRepository
     */
    private $orderRepository;
    /**
     * @var CreditCardTransaction
     */
    private $cardTransaction;
    /**
     * @var CreateCustomerService
     */
    private $customerService;
    /**
     * @var BoletoTransaction
     */
    private $boletoTransaction;

    /**
     * PaymentController constructor.
     * @param CreateOrderService $orderService
     * @param OrderRepository $orderRepository
     * @param CreditCardTransaction $cardTransaction
     * @param BoletoTransaction $boletoTransaction
     * @param CreateCustomerService $customerService
     */
    public function __construct(
        CreateOrderService $orderService,
        OrderRepository $orderRepository,
        CreditCardTransaction $cardTransaction,
        BoletoTransaction $boletoTransaction,
        CreateCustomerService $customerService
    ) {
        $this->orderService = $orderService;
        $this->orderRepository = $orderRepository;
        $this->cardTransaction = $cardTransaction;
        $this->boletoTransaction = $boletoTransaction;
        $this->customerService = $customerService;
    }

    /**
     * @SWG\Post(
     *     path="/v1/payment/credit-card",
     *     tags={"Payment"},
     *     summary="Create a CreditCard Payment",
     *     @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          required=true,
     *          @SWG\Schema(
     *              title="body",
     *              @SWG\Property(property="customer",  ref="#/definitions/Customer"),
     *              @SWG\Property(property="cart",      ref="#/definitions/Cart"),
     *              @SWG\Property(
     *                  required={"CreditCard"}, property="payment", title="Payment",
     *                  @SWG\Property(property="CreditCard", ref="#/definitions/CreditCardPayment")
     *              ),
     *              @SWG\Property(property="tracking",  ref="#/definitions/Tracking")
     *          )
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="A newly-created order"
     *     )
     * )
     * @param CreditCardPaymentRequest $request
     * @return \App\Services\Gateways\PaymentResponse
     */
    public function creditCard(CreditCardPaymentRequest $request)
    {
        $order = $this->getOrder($request);

        if ($order->isPaid()) {
            return new ApiResponse(false, "Order Already Paid", ["order"=>$order]);
        }

        $order->customer->update([
            "document_number" => $request->json("payment.CreditCard.document_number")
        ]);

        $payment = (object) $request->get("payment");

        $response = $this->cardTransaction
                            ->setPaymentsFromSite($order->customer->site)
                            ->payOrder(
                                $payment->CreditCard,
                                $order,
                                $payment->CreditCard['installments'],
                                "API"
                            );

        return new ApiResponse(
            $response->getStatus(),
            $response->getMessage(),
            [
                "order"     => $order->fresh(["customer", "products", "bundles"]),
                "payment"   => $response,
            ]
        );
    }

    /**
     * @SWG\Post(
     *     path="/v1/payment/boleto",
     *     tags={"Payment"},
     *     summary="Create a Boleto Payment",
     *     @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          required=true,
     *          @SWG\Schema(
     *              title="body",
     *              @SWG\Property(property="customer",  ref="#/definitions/Customer"),
     *              @SWG\Property(property="cart",      ref="#/definitions/Cart"),
     *              @SWG\Property(
     *                  required={"Boleto"}, property="payment", title="Payment",
     *                  @SWG\Property(property="Boleto", ref="#/definitions/BoletoPayment")
     *              ),
     *              @SWG\Property(property="tracking",  ref="#/definitions/Tracking")
     *          )
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="A newly-created order"
     *     )
     * )
     * @param BoletoPaymentRequest $request
     * @return \App\Services\Gateways\PaymentResponse
     */
    public function boleto(BoletoPaymentRequest $request)
    {
        $order = $this->getOrder($request);

        if ($order->isPaid()) {
            return new ApiResponse(false, "Order Already Paid", ["order"=>$order]);
        }

        $payment = (object) $request->get("payment");

        $order->customer->update([
            "document_number" => $payment->Boleto["document_number"]
        ]);

        $response = $this->boletoTransaction
                            ->setPaymentsFromSite($order->customer->site)
                            ->create(
                                $order->customer,
                                $order,
                                Carbon::createFromFormat("Y-m-d", $payment->Boleto["due_date"])
                            );

        return new ApiResponse(
            $response->getStatus(),
            $response->getMessage(),
            [
                "order"     => $order->fresh(["customer", "products", "bundles"]),
                "payment"   => $response,
            ]
        );
    }

    /**
     * @param Request $request
     * @return Order
     */
    private function getOrder(Request $request)
    {
        $cart = (object) $request->get("cart");

        if (isset($cart->order_id) && $cart->order_id) {
            $order      = $this->orderRepository->find($cart->order_id);
        } else {
            $customer = $this->customerService->create(array_merge($request->get("customer"), [
                "site_id" => $request->session()->get("site_id")
            ]));

            if ($customer->wasRecentlyCreated && $request->get("tracking")) {
                $customer->visits()->create($request->get("tracking"));
            }

            $totalOrder = 0;
            foreach ($cart->products as $product) {
                $totalOrder = $product["qty"] * $product["value"];
            };
            $order = $this->orderService->createFromProducts(
                $customer,
                $cart->products,
                $totalOrder,
                get_value($cart, "shipping", 0),
                get_value($cart, "discount", 0),
                OrderStatus::PENDING,
                "API"
            );
        }

        return $order;
    }
}
