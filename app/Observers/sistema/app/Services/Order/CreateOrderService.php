<?php

namespace App\Services\Order;

use App\Entities\Bundle;
use App\Entities\Order;
use App\Entities\Customer;
use App\Entities\OrderItemBundle;
use App\Entities\OrderItemProduct;
use App\Entities\Product;
use App\Entities\Status;
use App\Repositories\Orders\OrderRepository;
use App\Services\Product\CreateProductService;

class CreateOrderService
{
    /**
     * @var OrderRepository
     */
    private $orderRepository;
    /**
     * @var CreateProductService
     */
    private $productService;


    /**
     * CreateOrderService constructor.
     * @param OrderRepository $orderRepository
     * @param CreateProductService $productService
     */
    public function __construct(OrderRepository $orderRepository, CreateProductService $productService)
    {
        $this->orderRepository = $orderRepository;
        $this->productService = $productService;
    }

    public function update(Order $order, $total, $freight, $status = Status::PENDING, array $items = [], $paymentType = null, $gateway = null, $installments = null, $origin = null)
    {
        $order = $this->orderRepository->update([
            'total'         => $total,
            'payment_type_collection'  => $paymentType,
            'payment_type'  => $gateway ?: config("payment.types.$paymentType"),
            'freight_value' => $freight,
            'status'        => $status,
            'origin'        => $origin,
            'installments'  => $installments
        ], $order->id);

        if ($items) {
            $order->itemsBundle()->delete();
            $order->itemsProduct()->delete();
            $this->attachItems($order, $items);
        }

        return $order;
    }

    public function createFromProducts(Customer $customer, $products=[], $total, $freight = 0, $discount = 0, $status = Status::PENDING, $origin = null)
    {
        $order = $this->orderRepository->create([
            'total'         => $total,
            'status'        => $status,
            'customer_id'   => $customer->id,
            'freight_value' => $freight,
            'discount'      => $discount,
            'freight_type'  => "PAC",
            'tracking'      => "",
            'origin'        => $origin
        ]);

        foreach($products as $product){
            if($product instanceof Product){
                $this->attachProduct($product, $order, 1, $product->price);
            } else if (is_array($product) OR is_object($product)){
                if(is_array($product)){
                    $product = (object) $product;
                }
                $newProduct = $this->productService->create($product->sku, $customer->site->company->id, $product->name, $product->value);
                $this->attachProduct($newProduct, $order, $product->qty, $product->value);
            }
        }

        return $order;
    }

    public function create(Customer $customer, $total, $freight, $status = Status::PENDING, array $items = [],
                           $paymentType = null, $gateway = null, $installments = null, $origin = null)
    {
        $order = $this->orderRepository->create([
            'total'         => $total,
            'status'        => $status,
            'customer_id'   => $customer->id,
            'payment_type_collection'  => $paymentType,
            'payment_type'  => $gateway ?: config("payment.types.$paymentType"),
            'freight_value' => $freight,
            'freight_type'  => "PAC",
            'origin'        => $origin,
            'installments'  => $installments
        ]);

        if ($items) {
            $this->attachItems($order, $items);
        }

        return $order;
    }

    public function attachItems(Order $order, array $items)
    {
        foreach ($items as $item) {
            if(is_array($item)){
                $companyId = $order->customer->site->company->id;
                $product = $this->productService->create($item["sku"], $companyId, $item["name"], $item["value"]);
                $this->attachProduct($product, $order, $item["qty"], $item["value"]);
            } else {
                switch (get_class($item)) {
                    case Bundle::class:
                        $this->attachBundle($item, $order, 1, $item->price);
                        break;

                    case Product::class:
                        $this->attachProduct($item, $order, 1, $item->price);
                        break;

                    default:
                        throw new \Exception('Must be a instance of Bundle|Product');
                }
            }
        }
    }

    public function attachBundle(Bundle $bundle, $order, $qty = 1, $price = null)
    {
        return OrderItemBundle::create([
            'order_id'  => $order->id,
            'bundle_id' => $bundle->id,
            'qty'       => $qty,
            'price'     => $price ?: $bundle->price
        ]);
    }

    public function attachProduct(Product $product, $order, $qty = 1, $price = null)
    {
        return OrderItemProduct::create([
            'order_id'  => $order->id,
            'product_id'=> $product->id,
            'qty'       => $qty,
            'price'     => $price ?: $product->price
        ]);
    }
}