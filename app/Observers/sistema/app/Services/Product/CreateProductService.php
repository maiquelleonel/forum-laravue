<?php
/**
 * Created by PhpStorm.
 * User: dlima
 * Date: 1/24/18
 * Time: 09:26
 */

namespace App\Services\Product;


use App\Entities\Product;
use App\Repositories\Product\ProductRepository;

class CreateProductService
{
    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * CreateProductService constructor.
     * @param ProductRepository $productRepository
     */
    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * @param $sku
     * @param $name
     * @param $price
     * @param $companyId
     * @param int $cost
     * @param bool $isApp
     * @param null $description
     * @param null $label
     * @param int $inventory
     * @param null $ipi
     * @param null $image
     * @return Product
     */
    public function create($sku, $companyId, $name, $price, $cost = 0, $isApp = false, $description = null, $label = null, $inventory = 1, $ipi = null, $image = null)
    {
        if($product = $this->productRepository->findWhere(["sku"=>$sku, "company_id"=>$companyId])->first()){
            return $product;
        }

        return $this->productRepository->create(
            $this->makeData($sku, $companyId, $name, $price, $cost, $isApp, $description, $label, $inventory, $ipi, $image)
        );
    }

    /**
     * @param $sku
     * @param $companyId
     * @param $name
     * @param $price
     * @param int $cost
     * @param bool $isApp
     * @param null $description
     * @param null $label
     * @param int $inventory
     * @param int $ipi
     * @param null $image
     * @return array
     */
    public function makeData($sku, $companyId, $name, $price, $cost = 0, $isApp = false, $description = null, $label = null, $inventory = 1, $ipi = null, $image = null)
    {
        $data = [
            "sku"           => $sku,
            "company_id"    => $companyId,
            "name"          => $name,
            "price"         => $price,
            "cost"          => $cost
        ];

        if(!is_null($isApp)){
            $data["is_app"] = $isApp;
        }

        if(!is_null($description)){
            $data["description"] = $description;
        }

        if(!is_null($label)){
            $data["label"] = $label;
        }

        if(!is_null($inventory)){
            $data["inventory"] = $inventory;
        }

        if(!is_null($ipi)){
            $data["ipi"] = $ipi;
        }

        if(!is_null($image)){
            $data["image"] = $image;
        }

        return $data;
    }
}