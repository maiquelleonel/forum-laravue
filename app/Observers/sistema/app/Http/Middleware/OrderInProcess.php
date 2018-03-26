<?php

namespace App\Http\Middleware;

use App\Domain\OrderStatus;
use App\Entities\Order;
use App\Repositories\Additional\AdditionalRepository;
use Closure;

class OrderInProcess
{
    /**
     * @var AdditionalRepository
     */
    private $additionalRepository;

    /**
     * OrderInProcess constructor.
     * @param AdditionalRepository $additionalRepository
     */
    public function __construct(AdditionalRepository $additionalRepository)
    {
        $this->additionalRepository = $additionalRepository;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        switch ($request->method()) {
            case "GET":
                if ($response = $this->proccessGetRequest($request, $next)) {
                    return $response;
                }
                break;
        }

        return $next($request);
    }

    /**
     * Process GET Request
     * @param $request
     * @param Closure $next
     * @return mixed
     */
    private function proccessGetRequest($request, Closure $next)
    {
        $order  = Order::with('bundles')->find(session("order_id"));

        $routeName = $request->route()->getName();

        if ($order && $routeName) {
            // Tela inicial de pagamento
            if ($order->status == OrderStatus::INTEGRATED) {
                return redirect()->route("checkout::successPage");
            }

            switch ($routeName) {
                case "checkout::checkout.index":
                    return $this->processCheckoutRoute($order);

                case "checkout::checkout.retry":
                    return $this->processRetryPage($order);

                case "checkout::checkout.upsell":
                    return $this->processUpsellRoute($order);

                case "checkout::checkout.additional":
                    return $this->processAdditionalRoute($order);
            }
        }
    }

    /**
     * Process Checkout Route Rules
     * @param Order $order
     * @return \Illuminate\Http\RedirectResponse
     */
    private function processCheckoutRoute(Order $order)
    {
        if ($order->status == OrderStatus::CANCELED) {
            return redirect()->route("checkout::checkout.retry");
        } elseif (in_array($order->status, [OrderStatus::APPROVED, OrderStatus::AUTHORIZED])) {
            return redirect()->route("checkout::checkout.upsell");
        }
    }

    /**
     * Process Retry Route Rules
     * @param Order $order
     * @return \Illuminate\Http\RedirectResponse
     */
    private function processRetryPage(Order $order)
    {
        if (in_array($order->status, [OrderStatus::APPROVED, OrderStatus::AUTHORIZED])) {
            return redirect()->route("checkout::checkout.upsell");
        }
    }

    /**
     * Process Upsell Route Rules
     * @param Order $order
     * @return bool|\Illuminate\Http\RedirectResponse
     */
    private function processUpsellRoute(Order $order)
    {
        if ($order->status == OrderStatus::CANCELED) {
            return redirect()->route("checkout::checkout.retry");
        } elseif (!$order->canUpsell()) {
            return redirect()->route("checkout::checkout.additional");
        }

        return false;
    }

    /**
     * Process Additional Route Rules
     * @param Order $order
     * @return \Illuminate\Http\RedirectResponse
     */
    private function processAdditionalRoute(Order $order)
    {
        if ($order->status == OrderStatus::CANCELED) {
            return redirect()->route("checkout::checkout.retry");
        } elseif (! (bool) $this->additionalRepository->getNextAdditional($order)) {
            return redirect()->route("checkout::successPage");
        }
    }
}
