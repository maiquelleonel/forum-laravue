<?php

namespace App\Http\Controllers\Site;

use Illuminate\Http\Request;
use App\Http\Requests\Site\CustomerRequest;

use Illuminate\Http\Response;
use App\Entities\Customer;


class CustomerController extends BaseController
{
    /**
     * Display form to create a user
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create(Request $request)
    {

        $hidden = $request->all();

        return view('checkout::pages.preorder', compact("hidden"));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CustomerRequest $request
     * @return Response
     */
    public function store(CustomerRequest $request)
    {
        $data = $request->all();

        // UPDATE SESSION CUSTOMER
        if (app('customer_id')) {
            $customer = Customer::find( app('customer_id') );
            $customer->update( $data );
        } else if($request->has('email')){
            $customer = Customer::where([
                'site_id'   => $request->input('site_id'),
                'email'     => $request->input('email')
            ])->first();
            if ($customer) {
                $customer->fill( $data );
                $customer->save();
            }
        }
        if(!isset($customer)) {
            $customer = Customer::create($request->all());
        }

        session()->put('customer_id', $customer->id);

        if ($request->ajax()) {
            return response()->json($customer);
        }

        if (!$this->settings->isRemarketing()) {
            return redirect()->route("checkout::select.bundle", $request->only('bundle_id'));
        }

        return redirect()->route('checkout::checkout.index', $request->only('bundle_id'))   ;
    }

    /**
     * Store a new Customer from custom Landing Page
     * @param CustomerRequest $request
     * @return Response
     */
    public function storeFromExternalLP(CustomerRequest $request)
    {
        $request->merge([
            "redirect" => "checkout::select.bundle"
        ]);

        return $this->store($request);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param CustomerRequest $request
     * @return Response
     */
    public function update(CustomerRequest $request)
    {
        if ($customer = Customer::find( app('customer_id') )) {
            $customer->update($request->all());
            return response()->json(["status"=>true]);
        }
        return response()->json(["status"=>false]);
    }

}
