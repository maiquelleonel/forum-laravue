<?php

namespace App\Http\Controllers\Admin;

use App\Entities\Company;
use App\Http\Requests\Admin\ProductRequest;
use App\Entities\Product;

class ProductController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function index()
    {
        $products = Product::orderBy("name")->get();

        return view('admin.pages.product.index', compact('products'));
    }


    /**
     * Show form to edit product
     * @param $productId
     * @return \Illuminate\Http\Response
     */
    public function edit($productId)
    {
        $companies  = Company::all();
        $product    = Product::find( $productId );
        return view('admin.pages.product.edit', compact('product', 'companies'));
    }

    /**
     * Show form to create product
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $companies  = Company::all();
        return view('admin.pages.product.create', compact('companies'));
    }


    /**
     * Store new Product
     *
     * @param ProductRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory
     */
    public function store(ProductRequest $request)
    {
        $product = Product::create(
            $request->all()
        );

        return redirect()->route("admin:product.index");
    }

    /**
     * @param ProductRequest $request
     * @param $productId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(ProductRequest $request, $productId)
    {
        $product = Product::find($productId);
        $product->update( $request->all() );
        return redirect()->route("admin:product.index");
    }
}
