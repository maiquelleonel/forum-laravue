<?php

namespace App\Http\Controllers\Admin;

use App\Domain\BundleCategory;
use App\Entities\BundleGroup;
use App\Http\Requests\Admin\BundleRequest;
use App\Repositories\Bundle\BundleRepository;
use App\Entities\Bundle;
use App\Entities\Product;
use App\Entities\Site;

class BundleController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function index()
    {
        $siteBundles = BundleGroup::with('bundles.products')->has('bundles')->get();

        return view('admin.pages.bundle.index', compact('siteBundles'));
    }


    /**
     * Show form to edit product
     * @param $bundleId
     * @return \Illuminate\Http\Response
     */
    public function edit($bundleId)
    {
        $groups = BundleGroup::lists("name", "id");
        $categories = BundleCategory::labels();
        $bundle = Bundle::find( $bundleId );
        $products = Product::with("company")
                        ->orderBy('company_id')
                        ->orderBy('name')
                        ->get();

        return view('admin.pages.bundle.edit', compact('groups', 'bundle', 'categories', 'products'));
    }

    /**
     * Show form to create bundle
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $groups = BundleGroup::lists("name", "id");
        $categories = BundleCategory::labels();
        $products = Product::with("company")
                        ->orderBy('company_id')
                        ->orderBy('name')
                        ->get();

        return view('admin.pages.bundle.create', compact('groups', 'categories', 'products'));
    }


    /**
     * Store new Product
     *
     * @param BundleRequest $request
     * @param BundleRepository $bundleRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory
     */
    public function store(BundleRequest $request, BundleRepository $bundleRepository)
    {
        $bundle = $bundleRepository->create( $request->all() );
        \Cache::flush();
        return redirect()->route("admin:bundle.edit", $bundle);
    }

    /**
     * @param BundleRequest $request
     * @param $bundleId
     * @param BundleRepository $bundleRepository
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(BundleRequest $request, $bundleId, BundleRepository $bundleRepository)
    {
        $bundleRepository->update( $request->all(), $bundleId );
        \Cache::flush();
        return redirect()->back();
    }
}
