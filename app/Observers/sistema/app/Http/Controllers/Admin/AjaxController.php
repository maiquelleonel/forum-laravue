<?php

namespace App\Http\Controllers\Admin;

use App\Domain\BundleCategory;
use App\Entities\Bundle;
use App\Entities\Product;
use App\Http\Controllers\Controller as BaseController;
use App\Support\SiteSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AjaxController extends Controller
{
    /**
     * Get Bundles Without Upsell Bundle
     * @param $bundleGroupId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBundlesWithoutUpsell($bundleGroupId)
    {
        $bundles = Bundle::where("bundle_group_id", $bundleGroupId)
                            ->where("category", "!=", BundleCategory::UPSELL)
                            ->lists("name", "id");

        return $this->response(true, "", $bundles);
    }

    /**
     * Get Bundles Upsell
     * @param $bundleGroupId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBundlesUpsell($bundleGroupId)
    {
        $bundles = Bundle::where("bundle_group_id", $bundleGroupId)
                            ->where("category", BundleCategory::UPSELL)
                            ->lists("name", "id");

        return $this->response(true, "", $bundles);
    }

    /**
     * Get All Bundles
     * @param $bundleGroupId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBundles($bundleGroupId)
    {
        /**
         * @var $bundles Collection
         */
        $bundles = Bundle::where("bundle_group_id", $bundleGroupId)
                            ->orderBy("category", "name")
                            ->get();

        $array = [];

        foreach ($bundles as $bundle) {
            $array[$bundle->id] = sprintf("%s (%s)", $bundle->name, $bundle->category);
        }

        return $this->response(true, "", $array);
    }

    public function getProducts(Request $request, $company_id)
    {
        $search = $request->get("q");
        return Product::with("bundles")
                        ->where("company_id", $company_id)
                        ->where("name", "LIKE", "%".str_ireplace(" ", "%", $search)."%")
                        ->orderBy("name")
                        ->get();
    }
}
