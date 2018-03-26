<?php

namespace App\Http\Controllers\Site;

use Illuminate\Support\Facades\Crypt;
use App\Entities\Customer;
use App\Entities\Order;
use App\Http\Requests\TrackRequest;
use App\Services\TrackOrderService;

class TrackController extends BaseController
{

    public function index()
    {
        return "<h3>Sistema temporariamente indisponivel</h3>";
        return view("checkout::pages.track.index");
    }

    public function track( TrackOrderService $tracker, $orderId )
    {
        try {
            $order = Order::find( Crypt::decrypt( $orderId ));
            if (!$order) {
                throw new \Exception( "Order Not Found." );
            }
        } catch (\Exception $e) {
            return redirect()->route("track::index")
                             ->with("error", "Pedido não encontrado.");
        }

        $customer = $order->customer;

        $trackDetails = $tracker->findByOrder($order);

        return view("checkout::pages.track.order", compact("order", "customer", "trackDetails"));
    }

    public function orders(TrackRequest $request)
    {
        $customer = null;

        if ($phone = $request->get('phone')) {
            $customer = Customer::with('orders')->where('telephone', $phone)->first();
        } else if ($email = $request->get('email')) {
            $customer = Customer::with('orders')->where('email', $email)->first();
        }

        if (!$customer) {
            return redirect()->route("track::index")
                             ->with("error", "Cadastro não encontrado, verifique se suas informações estão coretas e tente novamente.");
        }

        if ($customer->orders->count() == 1) {
            return redirect()->route("track::order", Crypt::encrypt($customer->orders->first()->id) );
        }

        $orders = $customer->orders;
        return view("checkout::pages.track.orders", compact('customer', 'orders'));

    }
}