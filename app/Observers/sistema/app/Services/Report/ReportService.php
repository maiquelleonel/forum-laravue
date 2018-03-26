<?php

namespace App\Services\Report;

use App\Domain\OrderStatus;
use App\Entities\Product;
use App\Repositories\Criterias\DoesntHaveRelationCriteria;
use App\Repositories\Criterias\JustSitesInSessionCriteria;
use App\Repositories\Customers\Criteria\CustomerCounterCriteria;
use App\Repositories\Customers\CustomerRepository;
use App\Repositories\Orders\Criteria\BundleItemCriteria;
use App\Repositories\Orders\Criteria\OrderCounterCriteria;
use App\Repositories\Orders\Criteria\OriginCriteria;
use App\Repositories\Orders\Criteria\PaymentTypeCriteria;
use App\Repositories\Orders\Criteria\SiteCriteria;
use App\Repositories\Orders\Criteria\StatusCriteria;
use App\Repositories\Orders\Criteria\VendorCriteria;
use App\Repositories\Orders\Criteria\PaymentDateCriteria;
use App\Repositories\Orders\Criteria\WithoutUpsellCriteria;
use App\Repositories\Orders\Criteria\WithUpsellCriteria;
use App\Repositories\Orders\OrderRepository;
use App\Repositories\SalesCommission\Criteria\CommissionCounterCriteria;
use App\Repositories\SalesCommission\Criteria\CommissionStatusCriteria;
use App\Repositories\SalesCommission\Criteria\UserCriteria;
use App\Repositories\SalesCommission\SalesCommissionRepository;
use App\Repositories\Transaction\Criteria\TransactionCounterCriteria;
use App\Repositories\Transaction\Criteria\TransactionOriginCriteria;
use App\Repositories\Transaction\Criteria\TransactionTypeCriteria;
use App\Repositories\Transaction\Criteria\TransactionVendorCriteria;
use App\Repositories\Transaction\TransactionRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class ReportService
{
    /**
     * @var OrderRepository
     */
    private $orderRepository;
    /**
     * @var CustomerRepository
     */
    private $customerRepository;
    /**
     * @var TransactionRepository
     */
    private $transactionRepository;
    /**
     * @var SalesCommissionRepository
     */
    private $salesCommissionRepository;

    /**
     * ReportService constructor.
     * @param OrderRepository $orderRepository
     * @param CustomerRepository $customerRepository
     * @param TransactionRepository $transactionRepository
     * @param SalesCommissionRepository $salesCommissionRepository
     */
    public function __construct(OrderRepository $orderRepository,
                                CustomerRepository $customerRepository,
                                TransactionRepository $transactionRepository,
                                SalesCommissionRepository $salesCommissionRepository)
    {
        $this->orderRepository = $orderRepository;
        $this->customerRepository = $customerRepository;
        $this->transactionRepository = $transactionRepository;
        $this->salesCommissionRepository = $salesCommissionRepository;
    }

    /**
     * @param Carbon|null $from
     * @param Carbon|null $to
     * @return \Illuminate\Support\Collection
     */
    public function getTotalCustomers(Carbon $from = null, Carbon $to = null)
    {
        $this->customerRepository->resetCriteria()->resetModel();


        $response = $this->customerRepository
                         ->pushCriteria( new JustSitesInSessionCriteria() )
                         ->pushCriteria( new CustomerCounterCriteria($from, $to) )
                         ->all();

        return $this->parseResponse($response);
    }

    /**
     * @param Carbon|null $from
     * @param Carbon|null $to
     * @param array $status
     * @param bool $origin
     * @param mixed $paymentType
     * @param null $vendor
     * @param null $bundle
     * @param bool $byPaymentDay Contabilizar pela data do pagamento?
     * @return \Illuminate\Support\Collection
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function getTotalOrders(Carbon $from = null, Carbon $to = null, array $status = [], $origin = false, $paymentType = false, $vendor = null, $bundle = null, $byPaymentDay = false)
    {
        $this->orderRepository->resetCriteria()->resetModel();

        $response = $this->orderRepository
                            ->pushCriteria( new JustSitesInSessionCriteria() )
                            ->pushCriteria( new StatusCriteria($status) )
                            ->pushCriteria( new PaymentTypeCriteria($paymentType) )
                            ->pushCriteria( new OriginCriteria($origin) )
                            ->pushCriteria( new VendorCriteria($vendor) )
                            ->pushCriteria( new BundleItemCriteria($bundle) )
                            ->pushCriteria( new OrderCounterCriteria($from, $to, $byPaymentDay) )
                            ->all();

        return $this->parseResponse($response);
    }

    /**
     * @param Carbon|null $from
     * @param Carbon|null $to
     * @param bool $byPaymentDay
     * @param $user
     * @param $status
     * @param mixed $origin
     * @param mixed $paymentType
     * @param array $offers
     * @param string $groupByField
     * @return \Illuminate\Support\Collection
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function getTotalCommissions(Carbon $from = null, Carbon $to = null, $byPaymentDay = true, $user, $status, $origin=false, $paymentType=false, $offers=[], $groupByField = "sales_commission.created_at")
    {
        $this->salesCommissionRepository->resetCriteria()->resetModel();

        $response = $this->salesCommissionRepository
                            ->pushCriteria( new CommissionStatusCriteria($status) )
                            ->pushCriteria( new UserCriteria($user) )
                            ->pushCriteria( new PaymentTypeCriteria($paymentType) )
                            ->pushCriteria( new OriginCriteria($origin) )
                            ->pushCriteria( new SiteCriteria($offers) )
                            ->pushCriteria( new CommissionCounterCriteria($from, $to, $byPaymentDay, $groupByField) )
                            ->all();

        return $this->parseResponse($response);
    }

    public function getTotalProductsSold(Carbon $from = null, Carbon $to = null, $paymentType = false, $byPaymentDay = false)
    {
        $asItem     = $this->getTotalProductsSoldAsItem($from, $to, $paymentType, $byPaymentDay);
        $asBundle   = $this->getTotalProductsSoldAsBundle($from, $to, $paymentType, $byPaymentDay);

        $response   = collect($asItem)->keyBy("id");

        foreach($asBundle as $bundle){
            if($item = $response->get($bundle->id)){
                $item->qty += $bundle->qty;
            } else {
                $response->put($bundle->id, $bundle);
            }
        }

        return $response;
    }

    private function getTotalProductsSoldAsItem(Carbon $from = null, Carbon $to = null, $paymentType = false, $byPaymentDay = false)
    {
        $dateField = $byPaymentDay ? "orders.paid_at" : "orders.created_at";

        return \DB::table("products")
            ->select(
                "products.id", "products.sku", "products.name", "products.image",
                \DB::raw("sum(order_item_product.qty) as qty")
            )
            ->join("order_item_product", "order_item_product.product_id", "=", "products.id")
            ->join("orders", "order_item_product.order_id", "=", "orders.id")
            ->where(function($query) use ($paymentType){
                if($paymentType){
                    $query->where("orders.payment_type_collection", $paymentType);
                }
            })
            ->whereBetween($dateField, [$from, $to])
            ->whereIn("orders.status", OrderStatus::approved())
            ->groupBy("products.id")
            ->get();
    }

    private function getTotalProductsSoldAsBundle(Carbon $from = null, Carbon $to = null, $paymentType = false, $byPaymentDay = false)
    {
        $dateField = $byPaymentDay ? "orders.paid_at" : "orders.created_at";

        return \DB::table("products")
            ->select(
                "products.id", "products.sku", "products.name", "products.image",
                \DB::raw("sum(order_item_bundle.qty * bundle_product.product_qty) as qty")
            )
            ->join("bundle_product", "bundle_product.product_id", "=", "products.id")
            ->join("order_item_bundle", "order_item_bundle.bundle_id", "=", "bundle_product.bundle_id")
            ->join("orders", "order_item_bundle.order_id", "=", "orders.id")
            ->where(function($query) use ($paymentType){
                if($paymentType){
                    $query->where("orders.payment_type_collection", $paymentType);
                }
            })
            ->whereBetween($dateField, [$from, $to])
            ->whereIn("orders.status", OrderStatus::approved())
            ->groupBy("products.id")
            ->get();
    }

    /**
     * @param Carbon|null $from
     * @param Carbon|null $to
     * @param array $status
     * @param bool $origin
     * @param mixed $paymentType
     * @param null $vendor
     * @param null $bundle
     * @param bool $byPaymentDay
     * @return mixed
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function getOrders(Carbon $from = null, Carbon $to = null, array $status = [], $origin = false, $paymentType = false, $vendor = null, $bundle = null, $byPaymentDay = false)
    {
        $this->orderRepository->resetCriteria()->resetModel();

        return $this->orderRepository
            ->with('customer')
            ->pushCriteria( new JustSitesInSessionCriteria() )
            ->pushCriteria( new StatusCriteria($status) )
            ->pushCriteria( new PaymentTypeCriteria($paymentType) )
            ->pushCriteria( new OriginCriteria($origin) )
            ->pushCriteria( new VendorCriteria($vendor) )
            ->pushCriteria( new BundleItemCriteria($bundle) )
            ->pushCriteria( new PaymentDateCriteria($from, $to, $byPaymentDay) )
            ->all();
    }

    /**
     * @param Carbon|null $from
     * @param Carbon|null $to
     * @param array $status
     * @param bool $origin
     * @param mixed $paymentType
     * @param null $vendor
     * @param null $bundle
     * @param bool $byPaymentDay
     * @return mixed
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function getOrdersWithoutNotifications(Carbon $from = null, Carbon $to = null, array $status = [], $origin = false, $paymentType = false, $vendor = null, $bundle = null, $byPaymentDay = false)
    {
        $this->orderRepository->resetCriteria()->resetModel();

        return $this->orderRepository
            ->with('customer')
            ->pushCriteria( new JustSitesInSessionCriteria() )
            ->pushCriteria( new StatusCriteria($status) )
            ->pushCriteria( new DoesntHaveRelationCriteria('transactions.notifications') )
            ->pushCriteria( new PaymentTypeCriteria($paymentType) )
            ->pushCriteria( new OriginCriteria($origin) )
            ->pushCriteria( new VendorCriteria($vendor) )
            ->pushCriteria( new BundleItemCriteria($bundle) )
            ->pushCriteria( new PaymentDateCriteria($from, $to, $byPaymentDay) )
            ->all();
    }

    /**
     * @param Carbon $from
     * @param Carbon $to
     * @param $bundleId
     * @param array $status
     * @param bool $origin
     * @param mixed $paymentType
     * @param bool $byPaymentDay
     * @return \Illuminate\Support\Collection
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function getTotalOrdersByBundleWithUpsell(Carbon $from, Carbon $to, $bundleId, $status = [], $origin = false, $paymentType = false, $byPaymentDay = false)
    {
        $this->orderRepository->resetCriteria()->resetModel();

        $response = $this->orderRepository
                         ->pushCriteria( new JustSitesInSessionCriteria() )
                         ->pushCriteria( new WithUpsellCriteria($bundleId) )
                         ->pushCriteria( new StatusCriteria($status) )
                         ->pushCriteria( new PaymentTypeCriteria($paymentType) )
                         ->pushCriteria( new OriginCriteria($origin) )
                         ->pushCriteria( new OrderCounterCriteria($from, $to, $byPaymentDay) )
                         ->all();

        return $this->parseResponse($response);
    }

    /**
     * @param Carbon $from
     * @param Carbon $to
     * @param $bundleId
     * @param array $status
     * @param bool $origin
     * @param mixed $paymentType
     * @param bool $byPaymentDay
     * @return \Illuminate\Support\Collection
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function getTotalOrdersByBundleWithoutUpsell(Carbon $from, Carbon $to, $bundleId, $status = [], $origin = false, $paymentType = false, $byPaymentDay = false)
    {
        $this->orderRepository->resetCriteria()->resetModel();

        $response = $this->orderRepository
                         ->pushCriteria( new JustSitesInSessionCriteria() )
                         ->pushCriteria( new WithoutUpsellCriteria($bundleId) )
                         ->pushCriteria( new StatusCriteria($status) )
                         ->pushCriteria( new PaymentTypeCriteria($paymentType) )
                         ->pushCriteria( new OriginCriteria($origin) )
                         ->pushCriteria( new OrderCounterCriteria($from, $to, $byPaymentDay) )
                         ->all();

        return $this->parseResponse($response);
    }

    /**
     * Get All order that have upsell
     * @param Carbon $from
     * @param Carbon $to
     * @param array $status
     * @param bool $origin
     * @return \Illuminate\Support\Collection
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function getTotalOrdersWithUpsell(Carbon $from, Carbon $to, $status = [], $origin = false)
    {
        $this->orderRepository->resetCriteria()->resetModel();

        $response = $this->orderRepository
                         ->pushCriteria( new JustSitesInSessionCriteria() )
                         ->pushCriteria( new WithUpsellCriteria() )
                         ->pushCriteria( new StatusCriteria($status) )
                         ->pushCriteria( new OriginCriteria($origin) )
                         ->pushCriteria( new OrderCounterCriteria($from, $to, false) )
                         ->all();

        return $this->parseResponse($response);
    }

    /**
     * @param Carbon $from
     * @param Carbon $to
     * @param null $transactionType
     * @param bool|string|null $origin
     * @param null $vendor
     * @param bool $byPaymentDay
     * @return \Illuminate\Support\Collection
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function getTotalTransactions(Carbon $from, Carbon $to, $transactionType = null, $origin = false, $vendor = null, $byPaymentDay = false)
    {
        $this->transactionRepository->resetCriteria()->resetModel();

        $response = $this->transactionRepository
                         ->pushCriteria( new JustSitesInSessionCriteria() )
                         ->pushCriteria( new TransactionTypeCriteria($transactionType) )
                         ->pushCriteria( new TransactionOriginCriteria($origin) )
                         ->pushCriteria( new TransactionVendorCriteria($vendor) )
                         ->pushCriteria( new PaymentDateCriteria($from, $to, $byPaymentDay) )
                         ->pushCriteria( new TransactionCounterCriteria($from, $to) )
                         ->all();

        return $this->parseResponse($response);
    }

    public function getTotalsByCampaigns($from, $to, $byPaidAt=false, $source=null, $campaign=null, $media=null, $keyword=null, $origin=null, $customVars = [])
    {
        $raw = function($sql){
            return \DB::raw( $sql );
        };

        $dateColumn = $byPaidAt ? "orders.paid_at" : "orders.created_at";

        $response = \DB::table("orders")
                        ->select(
                            $raw("page_visit.utm_source"),
                            $raw("page_visit.utm_campaign"),
                            $raw("page_visit.utm_content"),
                            $raw("page_visit.utm_term"),

                            $raw("IF( orders.origin is NULL , 'site', orders.origin ) origin"),

                            $raw("SUM(IF(payment_type_collection='CreditCard',1,0)) as qtd_creditcard"),
                            $raw("SUM(IF(payment_type_collection='CreditCard',orders.total+orders.freight_value,0)) as total_creditcard"),

                            $raw("SUM(IF(payment_type_collection='Boleto',1,0)) as qtd_boleto"),
                            $raw("SUM(IF(payment_type_collection='Boleto',orders.total+orders.freight_value,0)) as total_boleto"),

                            $raw("SUM(IF(payment_type_collection='Pagseguro',1,0)) as qtd_pagseguro"),
                            $raw("SUM(IF(payment_type_collection='Pagseguro',orders.total+orders.freight_value,0)) as total_pagseguro")
                        )
                        ->leftJoin("page_visit", "page_visit.id", "=", "orders.page_visit_id")
                        ->whereBetween($dateColumn, [$from, $to])
                        ->whereIn("status", OrderStatus::approved())
                        ->where(function($query) use ($source, $campaign, $media, $keyword, $origin, $customVars){
                            if ($source) {
                                $query->where('page_visit.utm_source', $source);
                            }
                            if ($campaign) {
                                $query->where('page_visit.utm_campaign', $campaign);
                            }
                            if ($media) {
                                $query->where('page_visit.utm_content', $media);
                            }
                            if ($keyword) {
                                $query->where('page_visit.utm_term', $keyword);
                            }
                            if ($origin) {
                                if ($origin=="site") {
                                    $query->whereNull('orders.origin');
                                } else {
                                    $query->where('orders.origin', $origin);
                                }
                            }

                            foreach($customVars as $var=>$value){
                                if(in_array($var, ["custom_var_v1","custom_var_v2","custom_var_v3","custom_var_v4","custom_var_v5"])){
                                    if(!empty($value)){
                                        $query->where($var, $value);
                                    }
                                }
                            }
                        })
                        ->groupBy(
                            "page_visit.utm_source",
                            "page_visit.utm_campaign",
                            "page_visit.utm_content",
                            "page_visit.utm_term",
                            "orders.origin"
                        )->get();

        return $response;
    }

    public function getLeadsByCampaigns($from, $to, $source=null, $campaign=null, $media=null, $keyword=null, $customVars = [])
    {
        $raw = function($sql){
            return \DB::raw( $sql );
        };

        $response = \DB::table("page_visit")
                        ->select(
                            $raw("utm_source"),
                            $raw("utm_campaign"),
                            $raw("utm_content"),
                            $raw("utm_term"),
                            $raw("COUNT(DISTINCT visitor_id) AS unique_sessions"),
                            $raw("COUNT(DISTINCT customer_id) AS leads")
                        )
                        ->whereBetween("created_at", [$from, $to])
                        ->where(function($query) use ($source, $campaign, $media, $keyword, $customVars){
                            if ($source) {
                                $query->where('utm_source', $source);
                            }
                            if ($campaign) {
                                $query->where('utm_campaign', $campaign);
                            }
                            if ($media) {
                                $query->where('utm_content', $media);
                            }
                            if ($keyword) {
                                $query->where('utm_term', $keyword);
                            }

                            foreach($customVars as $var=>$value){
                                if(in_array($var, ["custom_var_v1","custom_var_v2","custom_var_v3","custom_var_v4","custom_var_v5"])){
                                    if(!empty($value)){
                                        $query->where($var, $value);
                                    }
                                }
                            }
                        })
                        ->groupBy(
                            "page_visit.utm_source",
                            "page_visit.utm_campaign",
                            "page_visit.utm_content",
                            "page_visit.utm_term"
                        )->get();

        return $response;
    }

    /**
     * @param Collection $responseCollection
     * @return \Illuminate\Support\Collection
     */
    private function parseResponse(Collection $responseCollection)
    {
        $field = "created";

        if($first = $responseCollection->first()){
            $field = array_keys($first->attributesToArray())[0];
        };

        $items = [];
        foreach($responseCollection as $model){
            $items[$model->{$field}] = (Object) [
                $field      => $model->{$field},
                'quantity'  => $model->qty ?: 0,
                'amount'    => $model->amount ?: 0
            ];
        };
        return collect($items);
    }
}