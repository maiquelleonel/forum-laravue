<?php

namespace App\Http\Middleware;

use Closure;
use Artesaos\Defender\Exceptions\ForbiddenException;
use Illuminate\Http\Request;
use App\Entities\Order;

class BlockByManyCreditCards
{
    public function handle($request, Closure $next)
    {
        if ($request->isMethod(Request::METHOD_POST) && env('APP_ENV') === 'production') {
            if ($order_id = session('order_id')) {
                $order       = Order::with('customer')->find($order_id);
                $total_cards = $order->customer->getCreditCards();
                if (count($total_cards) > 3) {
                    abort(403);
                }
            }
        }
        return $next($request);
    }
}
