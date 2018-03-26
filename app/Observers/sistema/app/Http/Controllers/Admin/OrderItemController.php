<?php
namespace App\Http\Controllers\Admin;

use App\Repositories\OrderItems\OrderItemBundleRepository;
use App\Repositories\OrderItems\OrderItemProductRepository;
use App\Repositories\Orders\OrderRepository;
use App\Http\Requests\Admin\Request;
use App\Entities\Bundle;
use App\Support\SiteSettings;

/**
 * Class OrderItemController
 * @package App\Http\Controllers\Admin
 */
class OrderItemController extends Controller
{
    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var OrderItemProductRepository
     */
    private $orderItemProduct;

    /**
     * @var OrderItemBundleRepository
     */
    private $orderItemBundle;

    /**
     * OrderItemController constructor.
     * @param SiteSettings $siteSettings
     * @param OrderItemProductRepository $orderItemProduct
     * @param OrderItemBundleRepository $orderItemBundle
     * @param OrderRepository $orderRepository
     */
    public function __construct(SiteSettings $siteSettings,
                                OrderItemProductRepository $orderItemProduct,
                                OrderItemBundleRepository $orderItemBundle,
                                OrderRepository $orderRepository)
    {
        parent::__construct($siteSettings);
        $this->orderRepository = $orderRepository;
        $this->orderItemProduct = $orderItemProduct;
        $this->orderItemBundle = $orderItemBundle;
    }

    /**
     * Store a new Product Item
     * @return \Illuminate\Contracts\Routing\ResponseFactory
     */
    public function storeProduct($orderId, Request $request)
    {
        $this->orderItemProduct->create(array_merge($request->all(),[
            'qty'      => $request->get("qty") ?: 1,
            'price'    => $request->get("price") ?: 0.01,
            'order_id' => $orderId
        ]));
        $this->orderRepository->updateTotalOrder($orderId);
        return back();
    }

    /**
     * Store a new Bundle Item
     * @return \Illuminate\Contracts\Routing\ResponseFactory
     */
    public function storeBundle($orderId, Request $request)
    {
        $bundle = Bundle::find( $request->get('bundle_id') );

        $this->orderItemBundle->create(array_merge($request->all(),[
            'qty'       => $request->get("qty") ?: 1,
            'order_id'  => $orderId,
            'price'     => $bundle->price ?: 0.01
        ]));

        $this->orderRepository->update([
            'freight_value' => $bundle->freight_value
        ], $orderId);

        $this->orderRepository->updateTotalOrder($orderId);
        return back();
    }

    /**
     * Destroy a item product by OrderItem ID
     * @param $orderItemId Id OrderItem
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyProduct($orderItemId)
    {
        try {
            $orderItem = $this->orderItemProduct->find($orderItemId);
            $this->orderItemProduct->delete($orderItemId);
            $this->orderRepository->updateTotalOrder($orderItem->order_id);
        } catch (\Exception $e) {
        }

        return back();
    }

    /**
     * Destroy a item bundle by OrderItem ID
     * @param $orderItemId Id OrderItem
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyBundle($orderItemId)
    {
        try {
            $orderItem = $this->orderItemBundle->find($orderItemId);
            $this->orderItemBundle->delete($orderItemId);
            $this->orderRepository->updateTotalOrder($orderItem->order_id);
        } catch (\Exception $e) {
        }

        return back();
    }
}