<?php
/**
 * Created by PhpStorm.
 * User: dlima
 * Date: 1/19/18
 * Time: 12:55
 */

namespace App\Http\Controllers\Api\V1;


use App\Domain\OrderStatus;
use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\V1\CustomerRequest;
use App\Http\Requests\Api\V1\OrderRequest;
use App\Http\Responses\ApiResponse;
use App\Repositories\Customers\CustomerRepository;
use App\Repositories\Orders\Criteria\SiteCriteria;
use App\Repositories\Orders\OrderRepository;
use App\Services\Customer\CreateCustomerService;
use App\Services\Order\CreateOrderService;
use Illuminate\Http\Request;

class OrderController extends BaseController
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
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * CustomerController constructor.
     * @param CreateOrderService $orderService
     * @param OrderRepository $orderRepository
     * @param CustomerRepository $customerRepository
     */
    public function __construct(CreateOrderService $orderService,
                                OrderRepository $orderRepository,
                                CustomerRepository $customerRepository)
    {
        $this->orderService = $orderService;
        $this->orderRepository = $orderRepository;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @SWG\Get(
     *     path="/v1/order",
     *     tags={"Order"},
     *     summary="List orders",
     *     @SWG\Response(
     *          response=200,
     *          description="List of orders",
     *          @SWG\Schema(ref="#/definitions/ArrayOfOrders")
     *      )
     * )
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function index(Request $request)
    {
        $orders = $this->orderRepository
                        ->with(["products", "bundles", "customer"])
                        ->pushCriteria(new SiteCriteria([$request->session()->get("site_id")]))
                        ->paginate(100);

        return $this->response(true, "success", $orders->toArray());
    }

    /**
     * @SWG\Get(
     *     path="/v1/order/{id}",
     *     tags={"Order"},
     *     summary="Fetch order",
     *     @SWG\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          type="integer",
     *          description="Order ID",
     * 		),
     *     @SWG\Response(
     *          response=200,
     *          description="Fetch Order",
     *          @SWG\Schema(ref="#/definitions/Order")
     *      )
     * )
     * @param Request $request
     * @param $orderId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function show(Request $request, $orderId)
    {
        $order =  $this->orderRepository
                          ->with(["products", "bundles", "customer"])
                          ->pushCriteria(new SiteCriteria([$request->session()->get("site_id")]))
                          ->find($orderId);

        return new ApiResponse(true, "OK", $order);
    }

    /**
     * @SWG\Post(
     *     path="/v1/order",
     *     tags={"Order"},
     *     summary="Create new order",
     *     @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          required=true,
     *          @SWG\Schema(ref="#/definitions/Cart")
     * 		),
     *     @SWG\Response(
     *          response=200,
     *          description="A newly-created order",
     *          @SWG\Schema(ref="#/definitions/Order")
     *      )
     * )
     * @param OrderRequest $request
     * @return ApiResponse
     */
    public function store(OrderRequest $request)
    {
        $customer = $this->customerRepository->findWhere([
            "id"        => $request->get("customer_id"),
            "site_id"   => $request->session()->get("site_id")
        ])->first();

        $total = 0;

        foreach($request->get("products") as $product){
            $total += $product["qty"] * $product["value"];
        }

        $order = $this->orderService->createFromProducts(
            $customer,
            $request->get("products"),
            $total,
            $request->get("shipping"),
            $request->get("discount"),
            OrderStatus::PENDING,
            "API"
        );

        return new ApiResponse(true, "OK", $order->fresh(["products", "bundles", "customer"]));
    }

    /**
     * @SWG\Put(
     *     path="/v1/order/{id}",
     *     tags={"Order"},
     *     summary="Update order",
     *     @SWG\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          type="integer",
     *          description="Order ID",
     * 	   ),
     *     @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          required=true,
     *          @SWG\Schema(ref="#/definitions/Cart")
     * 		),
     *     @SWG\Response(
     *          response=200,
     *          description="A updated customer",
     *          @SWG\Schema(ref="#/definitions/Order")
     *     )
     * )
     * @param OrderRequest $request
     * @param $orderId
     * @return ApiResponse
     */
    public function update(OrderRequest $request, $orderId)
    {
        $order = $this->orderRepository->find($orderId);
        $order = $this->orderService->update(
            $order, $request->get("total"), $request->get("shipping"), $order->status, $request->get("products")
        );

        return new ApiResponse(true, "OK", $order->fresh(["products", "bundles", "customer"]));
    }
}