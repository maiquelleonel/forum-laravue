<?php

namespace App\Http\Controllers\Admin;

use App\Domain\OrderStatus;
use App\Entities\Customer;
use App\Entities\Order;
use App\Entities\PageVisit;
use App\Entities\Product;
use App\Entities\SalesCommission;
use App\Entities\User;
use App\Repositories\Criterias\JustSitesInSessionCriteria;
use App\Repositories\Orders\Criteria\CampaignDataCriteria;
use App\Repositories\Orders\Criteria\OriginCriteria;
use App\Repositories\Orders\Criteria\PaymentDateCriteria;
use App\Repositories\Orders\Criteria\StatusCriteria;
use App\Repositories\Orders\OrderRepository;
use Artesaos\Defender\Exceptions\ForbiddenException;
use Carbon\Carbon;
use App\Http\Requests\Admin\Request;
use App\Repositories\Bundle\BundleRepository;
use App\Services\ChartService;
use App\Services\Report\ReportService;
use Illuminate\Support\Collection;
use App\Support\SiteSettings;
use App\Services\EvoluxLoginReport;

class ReportController extends Controller
{
    /**
     * @var ReportService
     */
    private $reportService;

    /**
     * @var ChartService
     */
    private $chartService;

    /**
     * @var BundleRepository
     */
    private $bundleRepository;

    /**
     * ReportController constructor.
     * @param SiteSettings $siteSettings
     * @param ReportService $reportService
     * @param ChartService $chartService
     * @param BundleRepository $bundleRepository
     */
    public function __construct(
        SiteSettings $siteSettings,
        ReportService $reportService,
        ChartService $chartService,
        BundleRepository $bundleRepository
    ) {
        parent::__construct($siteSettings);
        $this->reportService = $reportService;
        $this->chartService = $chartService;
        $this->bundleRepository = $bundleRepository;
    }

    /**
     * Charts Reports
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function charts(Request $request, $view = "")
    {
        $byPaymentDay   = true;
        $byCreatedDay   = false;

        list($from, $to) = $this->getDateInterval($request);

        $approvalStatus = OrderStatus::approved();

        $totalCustomers = $this->reportService->getTotalCustomers($from, $to);
        $totalOrders    = $this->reportService->getTotalOrders($from, $to);

        $transactionsCreditCard = $this->reportService->getTotalTransactions($from, $to, 'CreditCard');
        $approvalsBoleto     = $this->reportService->getTotalOrders(
            $from,
            $to,
            $approvalStatus,
            false,
            'Boleto',
            null,
            null,
            $byPaymentDay
        );
        $approvalsCreditCard = $this->reportService->getTotalOrders(
            $from,
            $to,
            $approvalStatus,
            false,
            'CreditCard',
            null,
            null,
            $byPaymentDay
        );
        $approvalsPagSeguro  = $this->reportService->getTotalOrders(
            $from,
            $to,
            $approvalStatus,
            false,
            'Pagseguro',
            null,
            null,
            $byPaymentDay
        );

        $transactionsBoleto         = $this->reportService->getTotalTransactions($from, $to, 'Boleto');
        $approvalsBoletoByCreatedAt = $this->reportService->getTotalOrders(
            $from,
            $to,
            $approvalStatus,
            false,
            'Boleto'
        );

        $approvalsBoletoSystem      = $this->reportService->getTotalOrders(
            $from,
            $to,
            $approvalStatus,
            'system',
            'Boleto',
            null,
            null,
            $byPaymentDay
        );
        $approvalsCreditCardSystem  = $this->reportService->getTotalOrders(
            $from,
            $to,
            $approvalStatus,
            'system',
            'CreditCard',
            null,
            null,
            $byPaymentDay
        );

        $approvals = $this->sumApprovals($from, $to, $approvalsBoleto, $approvalsCreditCard, $approvalsPagSeguro);

        $this->chartService->addLineChart('ApprovalsByType', [
            'Cartão'                => $approvalsCreditCard,
            'Boletos Compensados'   => $approvalsBoleto,
            'Pagseguro'             => $approvalsPagSeguro
        ], $from, $to, null, ['colors'=>['blue', 'green', 'yellow']]);

        $this->chartService->addAreaChart('ApprovalsBoleto', [
            'Boletos Emitidos'      => $transactionsBoleto,
            'Boletos Compensados'   => $approvalsBoleto,
            'Boletos Pagos'         => $approvalsBoletoByCreatedAt
        ], $from, $to, null, ['colors'=>['yellow', 'blue', 'green']]);

        $this->chartService->addAreaChart('ApprovalsBySubmits', [
            'Novos Clientes'    => $totalCustomers,
            'CC Submits'        => $transactionsCreditCard,
            'CC Aprovações'     => $approvalsCreditCard
        ], $from, $to, null, ['colors'=>['blue', 'yellow', 'green']]);

        return view($view ?: 'admin.pages.reports.charts', compact(
            'from',
            'to',
            'approvals',
            'approvalsCreditCard',
            'approvalsBoleto',
            'approvalsPagSeguro',
            'totalCustomers',
            'totalOrders',
            'approvalsCreditCardSystem',
            'approvalsBoletoSystem'
        ));
    }

    /**
     * Charts Reports
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function userMetrics(Request $request, $view = "")
    {
        $byPaymentDay   = true;
        $user           = auth()->user();

        list($from, $to) = $this->getDateInterval($request);

        $approvalStatus = OrderStatus::approved();

        $approvalsBoleto     = $this->reportService->getTotalOrders(
            $from,
            $to,
            $approvalStatus,
            false,
            'Boleto',
            $user->id,
            null,
            $byPaymentDay
        );
        $approvalsCreditCard = $this->reportService->getTotalOrders(
            $from,
            $to,
            $approvalStatus,
            false,
            'CreditCard',
            $user->id,
            null,
            $byPaymentDay
        );
        $approvalsPagSeguro  = $this->reportService->getTotalOrders(
            $from,
            $to,
            $approvalStatus,
            false,
            'Pagseguro',
            $user->id,
            null,
            $byPaymentDay
        );

        $approvals = $this->sumApprovals($from, $to, $approvalsBoleto, $approvalsCreditCard, $approvalsPagSeguro);

        $this->chartService->addLineChart('ApprovalsByType', [
            'Cartão'                => $approvalsCreditCard,
            'Boletos Compensados'   => $approvalsBoleto,
            'Pagseguro'             => $approvalsPagSeguro
        ], $from, $to, null, ['colors'=>['blue', 'green', 'yellow']]);

        return view($view ?: 'admin.pages.reports.user-metrics', compact(
            'from',
            'to',
            'approvals',
            'approvalsCreditCard',
            'approvalsBoleto',
            'approvalsPagSeguro'
        ));
    }

    /**
     * Charts Reports
     * @param Request $request
     * @param string $view
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function userCommissions(Request $request, $view = "")
    {
        $user           = auth()->user();
        $user->load("currency");

        list($from, $to) = $this->getDateInterval($request);

        $creditCardPayments = ["CreditCard", "Pagseguro"];
        $billetPayments     = ["Boleto"];

        $originSite         = [null, "promoexit"];
        $originCall         = ["system"];

        $approvedCreditCard  = $this->reportService->getTotalCommissions(
            $from,
            $to,
            false,
            $user->id,
            SalesCommission::STATUS_APPROVED,
            $originSite,
            $creditCardPayments,
            null,
            false
        )->first();

        $approvedBillet      = $this->reportService->getTotalCommissions(
            $from,
            $to,
            false,
            $user->id,
            SalesCommission::STATUS_APPROVED,
            $originSite,
            $billetPayments,
            null,
            false
        )->first();

        $approvedCallCenter  = $this->reportService->getTotalCommissions(
            $from,
            $to,
            false,
            $user->id,
            SalesCommission::STATUS_APPROVED,
            $originCall,
            false,
            null,
            false
        )->first();
        $approvedTotal       = (Object)[
            "quantity"  => $approvedCreditCard->quantity + $approvedBillet->quantity + $approvedCallCenter->quantity,
            "amount"    => $approvedCreditCard->amount + $approvedBillet->amount + $approvedCallCenter->amount,
        ];

        $paidCreditCard      = $this->reportService->getTotalCommissions(
            $from,
            $to,
            false,
            $user->id,
            SalesCommission::STATUS_PAID,
            $originSite,
            $creditCardPayments,
            null,
            false
        )->first();
        $paidBillet          = $this->reportService->getTotalCommissions(
            $from,
            $to,
            false,
            $user->id,
            SalesCommission::STATUS_PAID,
            $originSite,
            $billetPayments,
            null,
            false
        )->first();
        $paidCallCenter      = $this->reportService->getTotalCommissions(
            $from,
            $to,
            false,
            $user->id,
            SalesCommission::STATUS_PAID,
            $originCall,
            false,
            null,
            false
        )->first();
        $paidTotal       = (Object)[
            "quantity"  => $paidCreditCard->quantity + $paidBillet->quantity + $paidCallCenter->quantity,
            "amount"    => $paidCreditCard->amount + $paidBillet->amount + $paidCallCenter->amount,
        ];

        $approved   = $this->reportService->getTotalCommissions(
            $from,
            $to,
            false,
            $user->id,
            SalesCommission::STATUS_APPROVED,
            false,
            false
        );
        $paid       = $this->reportService->getTotalCommissions(
            $from,
            $to,
            true,
            $user->id,
            SalesCommission::STATUS_PAID,
            false,
            false
        );

        $leads      = PageVisit::query()->distinct("customer_id")
                                        ->where("custom_var_v1", $user->affiliate_id)
                                        ->whereBetween("created_at", [$from, $to])
                                        ->count("customer_id");

        $billetTransactions       = PageVisit::query()
                                        ->join("orders", "orders.page_visit_id", "=", "page_visit.id")
                                        ->where("payment_type_collection", "Boleto")
                                        ->where("custom_var_v1", $user->affiliate_id)
                                        ->whereBetween("page_visit.created_at", [$from, $to])
                                        ->count();

        $creditCardTransactions    = PageVisit::query()
                                        ->join("orders", "orders.page_visit_id", "=", "page_visit.id")
                                        ->whereIn("payment_type_collection", ["CreditCard", "Pagseguro"])
                                        ->where("custom_var_v1", $user->affiliate_id)
                                        ->whereBetween("page_visit.created_at", [$from, $to])
                                        ->count();

        $paidBilletTransactions     = $this->reportService->getTotalCommissions(
            $from,
            $to,
            false,
            $user->id,
            [SalesCommission::STATUS_PAID, SalesCommission::STATUS_APPROVED],
            false,
            $billetPayments,
            null,
            false
        )->first();

        $paidCreditCardTransactions = $this->reportService->getTotalCommissions(
            $from,
            $to,
            false,
            $user->id,
            [SalesCommission::STATUS_PAID, SalesCommission::STATUS_APPROVED],
            false,
            $creditCardPayments,
            null,
            false
        )->first();

        $user->with(["sites.leads"=>function ($lead) use ($from, $to) {
            $lead->whereBetween("created_at", [$from, $to]);
        }]);

        $offers = $user->offerSites;

        foreach ($offers as $offer) {
            $offer->commissions        = $offer->commissions()->whereBetween(
                "sales_commission.created_at",
                [ $from,  $to ]
            )->get();

            $offer->commissions_amount = $offer->commissions->sum("value");
            $offer->commissions_count  = $offer->commissions->count();

            $offer->leads              = $offer->leads()->whereBetween(
                "customers.created_at",
                [ $from, $to ]
            )->count();
        }


        $this->chartService->addLineChart('ApprovalsByDate', [
            trans("dashboard.approved_orders")      => $approved
        ], $from, $to, null, [ 'colors' => ['blue'] ]);

        $this->chartService->addLineChart('BilledByDate', [
            trans("dashboard.amount_orders", ["prefix" => trim($user->currency->prefix) ])
            => [$approved, 'amount', true]
        ], $from, $to, null, [ 'colors' => ['blue'] ]);

        return view($view ?: 'admin.pages.reports.user-commissions', compact(
            'user',
            'from',
            'to',
            'leads',
            'approved',
            'paid',
            'approvedCreditCard',
            'approvedBillet',
            'approvedCallCenter',
            'approvedTotal',
            'paidCreditCard',
            'paidBillet',
            'paidCallCenter',
            'paidTotal',
            'billetTransactions',
            'creditCardTransactions',
            'paidBilletTransactions',
            'paidCreditCardTransactions',
            'offers'
        ));
    }

    /**
     * Vendors Report
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function vendors(Request $request)
    {
        list($from, $to) = $this->getDateInterval($request);

        $approvalStatus = OrderStatus::approved();

        $ordersByVendor = collect();

        $users = User::whereHas("roles", function ($model) {
            return $model->where("name", "LIKE", "%vendedor%");
        })->get();

        foreach ($users as $vendor) {
            $totals = function (Collection $item) {
                return (Object)[
                    "quantity"  => $item->sum("quantity"),
                    "amount"    => $item->sum("amount"),
                ];
            };

            $creditCard = $this->reportService->getTotalOrders($from, $to, $approvalStatus, false, 'CreditCard', $vendor->id, null, true);
            $boleto     = $this->reportService->getTotalOrders($from, $to, $approvalStatus, false, 'Boleto', $vendor->id, null, true);
            $pagSeguro  = $this->reportService->getTotalOrders($from, $to, $approvalStatus, false, 'Pagseguro', $vendor->id, null, true);

            $data = (object) [
                'vendor'    => $vendor,

                'creditCard'=> $totals($creditCard),
                'boleto'    => $totals($boleto),
                'pagSeguro' => $totals($pagSeguro),
            ];

            $data->totals = (Object) [
                "quantity"  => $creditCard->sum('quantity') + $boleto->sum('quantity') + $pagSeguro->sum('quantity'),
                "amount"    => $creditCard->sum('amount') + $boleto->sum('amount') + $pagSeguro->sum('amount')
            ];

            $ordersByVendor->push($data);
        }

        $ordersByVendor = $ordersByVendor->sortByDesc("totals.amount");

        return view('admin.pages.reports.vendors', compact('from', 'to', 'ordersByVendor'));
    }

    /**
     * Bundles Report
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function bundlesSold(Request $request)
    {
        list($from, $to) = $this->getDateInterval($request);

        $approvalStatus = OrderStatus::approved();
        $byCreatedDay   = false;

        $bundles = $this->bundleRepository->getByCriteria(new JustSitesInSessionCriteria());

        foreach ($bundles as $bundle) {
            $bundle->sold   = $this->reportService->getTotalOrders($from, $to, $approvalStatus, false, false, null, $bundle->id, $byCreatedDay);
        }

        return view('admin.pages.reports.bundles-sold', compact('from', 'to', 'bundles'));
    }

    /**
     * Display Comparative Table
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function comparativeTables(Request $request)
    {
        list($from, $to) = $this->getDateInterval($request);

        $approvalStatus = OrderStatus::approved();

        $byPaymentDay   = true;
        $byCreatedDay   = false;
        $vendor         = null;
        $origin         = false;
        $bundle         = null;
        $paymentType    = null;

        $totalCustomers = $this->reportService->getTotalCustomers($from, $to);

        $transactionsBoleto         = $this->reportService->getTotalTransactions(
            $from,
            $to,
            'Boleto',
            $origin,
            $vendor,
            $byCreatedDay
        );

        $transactionsCreditCard     = $this->reportService->getTotalTransactions(
            $from,
            $to,
            'CreditCard',
            $origin,
            $vendor,
            $byCreatedDay
        );

        $approvalsBoleto     = $this->reportService->getTotalOrders(
            $from,
            $to,
            $approvalStatus,
            $origin,
            'Boleto',
            $vendor,
            $bundle,
            $byPaymentDay
        );

        $approvalsCreditCard = $this->reportService->getTotalOrders(
            $from,
            $to,
            $approvalStatus,
            $origin,
            'CreditCard',
            $vendor,
            $bundle,
            $byCreatedDay
        );

        $approvalsPagSeguro  = $this->reportService->getTotalOrders(
            $from,
            $to,
            $approvalStatus,
            $origin,
            'Pagseguro',
            $vendor,
            $bundle,
            $byCreatedDay
        );

        $approvalsBoletoSystem      = $this->reportService->getTotalOrders(
            $from,
            $to,
            $approvalStatus,
            'system',
            'Boleto',
            $vendor,
            $bundle,
            $byPaymentDay
        );

        $approvalsCreditCardSystem  = $this->reportService->getTotalOrders(
            $from,
            $to,
            $approvalStatus,
            'system',
            'CreditCard',
            $vendor,
            $bundle,
            $byCreatedDay
        );

        $approvalsPagSeguroSystem  = $this->reportService->getTotalOrders(
            $from,
            $to,
            $approvalStatus,
            'system',
            'Pagseguro',
            $vendor,
            $bundle,
            $byCreatedDay
        );

        $approvalsUpsell = $this->reportService->getTotalOrdersWithUpsell($from, $to, $approvalStatus, $origin);

        $approvals = $this->sumApprovals($from, $to, $approvalsBoleto, $approvalsCreditCard, $approvalsPagSeguro);

        return view('admin.pages.reports.comparative-table', compact(
            'from',
            'to',
            'totalCustomers',
            'totalOrders',
            'approvals',
            'approvalsUpsell',
            'transactionsBoleto',
            'transactionsCreditCard',
            'approvalsCreditCard',
            'approvalsBoleto',
            'approvalsPagSeguro',
            'approvalsBoletoSystem',
            'approvalsCreditCardSystem',
            'approvalsPagSeguroSystem'
        ));
    }

    /**
     * Extrato de vendas do vendedor
     * @param Request $request
     * @param $userId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Illuminate\Foundation\Application|mixed
     */
    public function extractSeller(Request $request, $userId)
    {
        list($from, $to) = $this->getDateInterval($request);

        if (!auth()->user()->isSuperUser() && $userId != auth()->user()->id) {
            throw new ForbiddenException;
        }

        $approvalStatus = OrderStatus::approved();

        $paymentDay = $request->get('payment_date') == 'payment_date';

        $extract = [
            'CreditCard' => $this->reportService->getOrders(
                $from,
                $to,
                $approvalStatus,
                false,
                'CreditCard',
                $userId,
                null,
                $paymentDay
            ),
            'Boleto'    => $this->reportService->getOrders(
                $from,
                $to,
                $approvalStatus,
                false,
                'Boleto',
                $userId,
                null,
                $paymentDay
            ),
            'Pagseguro' => $this->reportService->getOrders(
                $from,
                $to,
                $approvalStatus,
                false,
                'Pagseguro',
                $userId,
                null,
                $paymentDay
            ),
            'Outras Formas' => $this->reportService->getOrders(
                $from,
                $to,
                $approvalStatus,
                false,
                null,
                $userId,
                null,
                $paymentDay
            )
        ];

        $quantityOrders = 0;
        $totalOrders = 0;

        foreach ($extract as $orders) {
            $quantityOrders+= $orders->count();
            $totalOrders+= $orders->sum('total') + $orders->sum('freight_value') - $orders->sum('discount');
        }

        $user = User::withTrashed()->find($userId);

        $extract['Boletos Pendentes'] = $this->reportService->getOrders(
            $from,
            $to,
            ['pendente', 'cancelado'],
            false,
            'Boleto',
            $userId,
            null,
            $paymentDay
        );

        return view('admin.pages.reports.extract-seller', compact(
            'from',
            'to',
            'user',
            'extract',
            'quantityOrders',
            'totalOrders'
        ));
    }

    public function campaignsOrders(Request $request)
    {
        list($from, $to) = $this->getDateInterval($request);

        list($listSources, $listCampaigns, $listMedias, $listKeywords, $listOrigins) = $this->getLists();

        $byPaidAt = $request->get('payment_date', 'payment_date') == 'payment_date';

        $totals = collect($this->reportService->getTotalsByCampaigns(
            $from,
            $to,
            $byPaidAt,
            $request->get('utm_source'),
            $request->get('utm_campaign'),
            $request->get('utm_content'),
            $request->get('utm_term'),
            $request->get('origin'),
            $request->all()
        ));

        return view('admin.pages.reports.campaigns', compact(
            'from',
            'to',
            'totals',
            'listSources',
            'listCampaigns',
            'listMedias',
            'listKeywords',
            'listOrigins'
        ));
    }

    public function detailedCampaignsOrders(Request $request, OrderRepository $orderRepository)
    {
        list($from, $to) = $this->getDateInterval($request);

        list($listSources, $listCampaigns, $listMedias, $listKeywords, $listOrigins) = $this->getLists();

        $byPaidAt = $request->get('payment_date', 'payment_date') == 'payment_date';

        $status = OrderStatus::all(true);

        $orders = $orderRepository
                        ->pushCriteria(new PaymentDateCriteria($from, $to, $byPaidAt))
                        ->pushCriteria(new CampaignDataCriteria($request->all()))
                        ->pushCriteria(new OriginCriteria($request->get('origin')))
                        ->pushCriteria(new StatusCriteria($request->get('status', [])))
                        ->paginate(10);

        return view('admin.pages.reports.campaigns.orders', compact(
            'from',
            'to',
            'orders',
            'status',
            'listSources',
            'listCampaigns',
            'listMedias',
            'listKeywords',
            'listOrigins'
        ));
    }

    public function campaignsLeads(Request $request)
    {
        list($from, $to) = $this->getDateInterval($request);

        list($listSources, $listCampaigns, $listMedias, $listKeywords, $listOrigins) = $this->getLists();

        $totals = collect($this->reportService->getLeadsByCampaigns(
            $from,
            $to,
            $request->get('utm_source'),
            $request->get('utm_campaign'),
            $request->get('utm_content'),
            $request->get('utm_term'),
            $request->all()
        ));

        return view('admin.pages.reports.campaigns-leads', compact(
            'from',
            'to',
            'totals',
            'listSources',
            'listCampaigns',
            'listMedias',
            'listKeywords',
            'listOrigins'
        ));
    }

    public function productsSold(Request $request)
    {
        list($from, $to) = $this->getDateInterval($request);

        $products = Product::all();

        $productsCreditCard = $this->reportService->getTotalProductsSold($from, $to, "CreditCard",  false);
        $productsBoleto     = $this->reportService->getTotalProductsSold($from, $to, "Boleto",      true);
        $productsPagSeguro  = $this->reportService->getTotalProductsSold($from, $to, "PagSeguro",   true);

        foreach($products as $product){
            $product->qty = 0;

            if ($creditCard = $productsCreditCard->get($product->id)) {
                $product->qty += $creditCard->qty;
            }
            if ($boleto = $productsBoleto->get($product->id)) {
                $product->qty += $boleto->qty;
            }
            if ($pagSeguro = $productsPagSeguro->get($product->id)) {
                $product->qty += $pagSeguro->qty;
            }
        }

        return view('admin.pages.reports.products-sold', compact(
            "from", "to", "products"
        ));
    }

    /**
     * @param Request $request
     * @return array of two Carbon Instances (FROM and TO)
     */
    private function getDateInterval(Request $request)
    {
        if (!$request->has('from')) {
            $from = Carbon::now()->subDays(7)->setTime(0, 0, 0);
        } else {
            $from = Carbon::createFromFormat('d/m/Y', $request->input('from'))->setTime(0, 0, 0);
        }

        if (!$request->has('to')) {
            $to = Carbon::now()->setTime(23, 59, 59);
        } else {
            $to = Carbon::createFromFormat('d/m/Y', $request->input('to'))->setTime(23, 59, 59);
        }

        if ($from->gte($to)) {
            $from = $to->copy()->setTime(0, 0, 0);
        }

        return [$from, $to];
    }

    public function sumApprovals($from, $to, $approvalsBoleto, $approvalsCreditCard, $approvalsPagSeguro)
    {
        $approvals = new Collection;

        foreach (new \DatePeriod($from, new \DateInterval('P1D'), $to) as $date) {
            $date = $date->format('d/m/Y');

            $value = function ($date, $key, $collection) {
                return isset($collection[$date]) ? $collection[$date]->$key : 0;
            };

            $quantity = $value($date, 'quantity', $approvalsBoleto)
                + $value($date, 'quantity', $approvalsCreditCard)
                + $value($date, 'quantity', $approvalsPagSeguro);

            $amount = $value($date, 'amount', $approvalsBoleto)
                + $value($date, 'amount', $approvalsCreditCard)
                + $value($date, 'amount', $approvalsPagSeguro);

            $approvals->put($date, (Object) [
                "created"   => $date,
                "quantity"  => $quantity,
                "amount"    => $amount
            ]);
        }

        return $approvals;
    }

    private function getLists()
    {
        $origins = collect(['site'=>'Site', 'promoexit'=>'Promoexit', 'system'=>'Call Center',
        'remarketing'=>'Remarketing', 'email'=>'Email']);
        $nulls   = collect([""=>"Todos"]);

        $cacheMinutes = 5;

        $listSources    = \Cache::remember('sources', $cacheMinutes, function () use ($nulls) {
            $sources = PageVisit::getSources();
            return $nulls->union($sources->combine($sources));
        });

        $listCampaigns  = \Cache::remember('campaigns', $cacheMinutes, function () use ($nulls) {
            $campaigns = PageVisit::getCampaigns();
            return $nulls->union($campaigns->combine($campaigns));
        });

        $listMedias     = \Cache::remember('medias', $cacheMinutes, function () use ($nulls) {
            $medias = PageVisit::getMedias();
            return $nulls->union($medias->combine($medias));
        });

        $listKeywords   = \Cache::remember('keywords', $cacheMinutes, function () use ($nulls) {
            $keywords = PageVisit::getKeywords();
            return $nulls->union($keywords->combine($keywords));
        });

        $listOrigins    = $nulls->union($origins);

        return [
            $listSources, $listCampaigns, $listMedias, $listKeywords, $listOrigins
        ];
    }

    public function evoluxLogin(Request $request)
    {
        $sellers = User::whereNotNull('evolux_login')->orderBy('name')->lists("name", "evolux_login")->toArray();
        list($from, $to) = $this->getDateInterval($request);
        $path = 'admin.pages.reports.evolux-login';
        return view($path, compact('sellers', 'from', 'to'));
    }

    public function evoluxLoginProcess(Request $request)
    {
        $params     = $request->all();
        $user       = $params['seller'];
        $start_date = join('-', array_reverse(explode('/', $params['from'])));
        $report     = new EvoluxLoginReport($user, $start_date);
        $path       = $report->make();
        return response()->download($path);
    }

}
