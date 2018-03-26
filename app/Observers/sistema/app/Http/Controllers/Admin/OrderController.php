<?php
namespace App\Http\Controllers\Admin;

use App\Domain\OrderStatus;
use App\Entities\User;
use App\Http\Requests\Admin\OrderRequest;
use App\Jobs\SendOrderToErp;
use App\Repositories\Criterias\JustSitesInSessionCriteria;
use App\Repositories\Customers\Criteria\CustomerDataCriteria;
use App\Repositories\Orders\Criteria\InvoiceIdCriteria;
use App\Repositories\Orders\Criteria\StatusCriteria;
use App\Repositories\Orders\Criteria\WithoutInvoiceCriteria;
use App\Repositories\Orders\OrderRepository;
use App\Repositories\Product\ProductRepository;
//use App\Services\ErpIntegration\BlingExportService;

use App\Http\Requests;
use App\Entities\Order;
use App\Support\SiteSettings;
use Carbon\Carbon;
use Prettus\Repository\Criteria\RequestCriteria;

class OrderController extends Controller
{
    /**
     * @var OrderRepository
     */
    private $orderRepository;
    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * OrderController constructor.
     * @param SiteSettings $siteSettings
     * @param OrderRepository $orderRepository
     * @param ProductRepository $productRepository
     */
    public function __construct(
        SiteSettings $siteSettings,
        OrderRepository $orderRepository,
        ProductRepository $productRepository
    ) {
        parent::__construct($siteSettings);
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param OrderRequest $request
     * @return \Illuminate\Http\Response
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function index(OrderRequest $request)
    {
        $status = OrderStatus::all(true);

        $sortableFields = $this->orderRepository->getSortableFields();

        $orders = $this->orderRepository
                        ->with(['customer.site', 'seller'])
                        ->pushCriteria(new RequestCriteria($request))
                        ->pushCriteria(new InvoiceIdCriteria($request->get('ids')))
                        ->pushCriteria(new JustSitesInSessionCriteria())
                        ->pushCriteria(new StatusCriteria($request->get('status', [])))
                        ->pushCriteria(new CustomerDataCriteria($request))
                        ->paginate();

        return view('admin.pages.order.index', compact('orders', 'status', 'sortableFields'));
    }

    /**
     * Display a listing of the resource.
     *
     * @param OrderRequest $request
     * @return \Illuminate\Http\Response
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function withoutInvoice(OrderRequest $request)
    {
        $status = [OrderStatus::INTEGRATED => OrderStatus::INTEGRATED];

        request()->merge([
            "status" => $status
        ]);

        $sortableFields = $this->orderRepository->getSortableFields();

        $orders = $this->orderRepository
                        ->with(['customer.site', 'seller'])
                        ->pushCriteria(new RequestCriteria($request))
                        ->pushCriteria(new InvoiceIdCriteria($request->get('ids')))
                        ->pushCriteria(new JustSitesInSessionCriteria())
                        ->pushCriteria(new CustomerDataCriteria($request))
                        ->pushCriteria(new CustomerDataCriteria($request))
                        ->pushCriteria(new StatusCriteria([OrderStatus::INTEGRATED]))
                        ->pushCriteria(new WithoutInvoiceCriteria())
                        ->paginate();

        return view('admin.pages.order.index', compact('orders', 'status', 'sortableFields'));
    }


    /**
     * Show Order by OrderId
     * @param $orderId
     * @return \Illuminate\Http\Response
     */
    public function show($orderHash)
    {
        $order = Order::with('customer', 'seller', 'transactions.user', 'audits.user', 'visit.pageViews')
            ->where('hash', $orderHash)->first();

        if (!$order) {
            $type = 'Pedido';
            return view('admin.errors.404', compact('type'));
        }

        $this->checkForLog($order);
        $noVendor = collect([''=>":: NINGUÉM ::"]);
        $vendors  = $noVendor->union(User::query()->orderBy('name')->lists("name", "id"));

        $status = OrderStatus::allowChangeTo($order->status, true);

        $itemsAudits = collect();

        foreach ($order->itemsBundle()->withTrashed()->with("audits.user")->get() as $bundle) {
            $itemsAudits = $itemsAudits->merge($bundle->audit);
        }

        foreach ($order->itemsProduct()->withTrashed()->with("audits.user")->get() as $product) {
            $itemsAudits = $itemsAudits->merge($product->audits);
        }

        $itemsAudits = $itemsAudits->sortBy("created_at", SORT_REGULAR, true);

        $mainProduct = $order->customer->site->mainProduct;

        return view('admin.pages.order.show', compact('order', 'products', 'vendors', 'status', 'itemsAudits', 'mainProduct'));
    }


    /**
     * Store new Order
     *
     * @param OrderRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory
     */
    public function store(OrderRequest $request)
    {
        $data = $request->all();
        $data['user_id'] = auth()->user()->id;
        $data['origin'] = 'system';

        if ($order = $this->orderRepository->create($data)) {
            return redirect()->route('admin:orders.show', $order);
        }

        return back();
    }

    /**
     * @param OrderRequest $request
     * @param $orderId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(OrderRequest $request, $orderId)
    {
        $this->orderRepository->update($request->all(), $orderId);
        return back();
    }

    /**
     * Integrate Order ( Send to Bling )
     * @param $orderId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function integrate($orderId)
    {
        $order = Order::find($orderId);

        if ($order->customer->site->erpSetting) {
            $job = new SendOrderToErp($order, $order->customer->site->erpSetting);
            $job->handle();

            if($order->lastAnalyze()->where("status", false)->count() === 0 && $order->status == OrderStatus::INTEGRATED) {
                session()->flash("success", "Pedido Integrado com sucesso!");
            } else {
                session()->flash("error", "Falha ao integrar pedido, verifique os erros nas validaçõs abaixo e tente novamente.");
            }
        } else {
            session()->flash("error", "Não existe integração para o site que o cliente pertence");
        }

        return back();
    }

    /**
     * Integrate Order ( Send to Bling )
     * @param $orderId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function integrateNow($orderId)
    {
        $order = Order::find($orderId);

        if ($order->customer->site->erpSetting) {
            $setting = $order->customer->site->erpSetting;
            $setting->run_validations = false;
            $sender = new SendOrderToErp($order, $setting);
            $sender->handle();
        } else {
            session()->flash("error", "Não existe integração para o site que o cliente pertence");
        }

        return back();
    }

    public function storeUpsell($orderId)
    {
        $errorMessage = "Pedido não existe";

        if ($order = $this->orderRepository->find($orderId)) {
            if ($order->userCanUpsell()) {
                $data = [
                    'user_id'           => auth()->user()->id,
                    'origin'            => 'system',
                    'customer_id'       => $order->customer_id,
                    'upsell_order_id'   => $orderId
                ];

                if ($createdOrder = $this->orderRepository->create($data)) {
                    return redirect()->route('admin:orders.show', $createdOrder);
                }
            }
            $errorMessage = "Não é mais possível criar um pedido complementar deste pedido";
        }

        session()->flash("error", $errorMessage);
        return redirect()->back();
    }

    private function checkForLog($order)
    {
        if (auth()->user()
            && !request()->server("HTTP_REFERRER")
            && $order
            && $order->origin != "system"
            && $order->created_at->addMinutes(10) > Carbon::now()) {
            try {
                \Log::critical(implode(" | ", [
                    "ACESSO DIRETO",
                    auth()->user()->name,
                    request()->fullUrl()
                ]));
            } catch (\Exception $e) {
                //
            }
        }
    }
}
