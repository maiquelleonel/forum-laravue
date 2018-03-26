<?php

namespace App\Entities;

use App\Domain\OrderStatus;
use App\Domain\TransactionType;
use App\Services\Gateways\PaymentResponse;
use Illuminate\Database\Eloquent\Collection;
use Laracasts\Presenter\PresentableTrait;
use App\Presenters\OrderPresenter;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 *
 * @SWG\Definition(
 *      required={
 *          "products"
 *      },
 *      definition="Cart",
 *      @SWG\Property(property="order_id", type="integer", description="Order ID", example=""),
 *      @SWG\Property(property="shipping", type="number", description="Cart Shipping Value"),
 *      @SWG\Property(property="discount", type="number", description="Cart Discount Value"),
 *      @SWG\Property(
 *          property="products", type="array", description="Products's list", title="ProductItem",
 *              @SWG\Items(ref="#/definitions/ProductItem")
 *      )
 * )
 *
 * @SWG\Definition(
 *      definition="Order",
 *      @SWG\Property(property="customer_id", type="number", description="Customer ID"),
 *      @SWG\Property(property="total", type="number", description="Order Total Value"),
 *      @SWG\Property(property="discount", type="number", description="Order Discount Value"),
 *      @SWG\Property(property="freight_value", type="number", description="Order Shipping Value"),
 *      @SWG\Property(property="freight_type", type="string", description="Order Shipping Type"),
 *      @SWG\Property(property="status", type="string", description="Order Status"),
 *      @SWG\Property(property="tracking", type="string", description="Order Shipping Tracking Code"),
 *      @SWG\Property(property="origin", type="number", description="Order Origin"),
 *      @SWG\Property(property="installments", type="number", description="Payment Installments"),
 *      @SWG\Property(property="user_id", type="number", description="User who sell"),
 *      @SWG\Property(property="payment_type", type="string", description="Payment Gateway"),
 *      @SWG\Property(property="payment_type_collection", type="string", description="Payment Method"),
 *      @SWG\Property(property="invoice_id", type="string", description="Generated Invoice ID (Sales Order)"),
 *      @SWG\Property(property="invoice_number",
 *      type="string", description="Generated Invoice Number (Invoice Number)"),
 *      @SWG\Property(property="paid_at", type="string", description="Payment Date"),
 *      @SWG\Property(property="created_at", type="string", description="Created Date"),
 *      @SWG\Property(property="updated_at", type="string", description="Last Update Date"),
 *      @SWG\Property(
 *          property="products", type="array", description="Products's list", title="Product",
 *              @SWG\Items(ref="#/definitions/Product")
 *      ),
 *      @SWG\Property(property="customer", ref="#/definitions/Customer")
 * )
 * @SWG\Definition(
 *      definition="ArrayOfOrders",
 *      type = "array",
 *      @SWG\Items(ref = "#/definitions/Order")
 * )
 *
 * @property integer site_id
 * @property integer id
 * @property Collection commissions
 * @property Customer customer
 * @property string origin
 * @property string payment_type_collection
 * @property float total
 * @property float freight_value
 * @property float discount
 * @property string payment_type
 * @property PageVisit visit
 * @property Site site
 * @property Order upsellOrder
 * @property float sub_total
 * @property Collection upsellOrders
 * @property Collection approvedUpsellOrders
 */
class Order extends Model implements AuditableContract
{
    use PresentableTrait, Auditable;

    protected $table = "orders";

    protected $presenter = OrderPresenter::class;

    protected $fillable = [
        'customer_id',
        'total',
        'status',
        'freight_value',
        'freight_type',
        'tracking',
        'installments',
        'origin',
        'user_id',
        'payment_type',
        'payment_type_collection',
        'invoice_id',
        'invoice_number',
        'paid_at',
        'created_at',
        'updated_at',
        'click_id',
        'source',
        'ip',
        'user_agent',
        'device',
        'discount',
        'page_visit_id',
        'upsell_order_id'
    ];

    protected $hidden = [
        'hash',
        'page_visit_id',
        'click_id',
        'source',
        'ip',
        'invoice_id',
        'invoice_number',
        'user_agent',
        'device'
    ];

    protected $dates = [
        'paid_at',
        'created_at',
        'updated_at'
    ];

    protected $appends = [
        'sub_total'
    ];

    public function getRouteKeyName()
    {
        return 'hash';
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function site()
    {
        return $this->customer->belongsTo(Site::class);
    }

    public function itemsBundle()
    {
        return $this->hasMany(OrderItemBundle::class);
    }

    public function bundles()
    {
        return $this->belongsToMany(Bundle::class, 'order_item_bundle')
                    ->whereNull("deleted_at")
                    ->withPivot("price", "qty");
    }

    public function itemsProduct()
    {
        return $this->hasMany(OrderItemProduct::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_item_product')
                    ->whereNull("deleted_at")
                    ->withPivot("price", "qty");
    }

    public function seller()
    {
        return $this->belongsTo(User::class, "user_id")->withTrashed();
    }

    public function notifications()
    {
        return $this->hasMany(OrderNotification::class);
    }

    public function visit()
    {
        return $this->belongsTo(PageVisit::class, "page_visit_id");
    }

    public function commissions()
    {
        return $this->hasMany(SalesCommission::class, "order_id");
    }

    public function cartItems()
    {
        $items = collect();

        $cartItem = function ($id, $description, $qty, $unitPrice) {
            return (Object) [
                "id"            => $id,
                "description"   => $description,
                "qty"           => $qty,
                "price"         => $unitPrice,
                "total"         => $qty * $unitPrice
            ];
        };

        foreach ($this->itemsProduct as $item) {
            $items->push($cartItem(
                $item->product_id, $item->product->name, $item->qty, $item->price
            ));
        }

        foreach ($this->itemsBundle as $item) {
            foreach ($item->bundle->products as $product) {
                $items->push($cartItem(
                    $product->id, $product->name, $product->pivot->product_qty, $product->pivot->product_price
                ));
            }
        }

        return $items;
    }

    /**
     * Checa se existem e é possível efetuar upsell do pacote adquirido
     * @return bool
     */
    public function canUpsell()
    {
        return $this->bundles && $this->bundles->first()->upsell && $this->bundles->first()->upsell->count() > 0;
    }

    /**
     * Checa se é possível
     * @return bool
     */
    public function userCanUpsell()
    {
        $total = $this->total - $this->discount;
        $site  = $this->customer->site;
        $maxBundles     = $site->bundles->max("price");
        $maxAdditional  = $site->additionals->max('qty_max') * $site->additionals->max('price');

        return $this->origin != "system"
                    && !$this->user_id
                    && in_array($this->status, [OrderStatus::APPROVED, OrderStatus::AUTHORIZED])
                    && $this->isCreditCard()
                    && !$this->upsellOrder
                    && $this->upsellOrders->count() == 0
                    && ($total < $maxBundles || $total < $maxBundles + $maxAdditional);
    }

    public function analyzes()
    {
        return $this->hasMany(OrderAnalyzeResponse::class);
    }

    public function lastAnalyze()
    {
        return $this->analyzes->sortByDesc("batch")->groupBy("batch")->first();
    }

    public function validCreditCardTransaction()
    {
        // MundiPagg valid query
        $mundipaggQuery = function ($query) {
            $query->where('response_json', 'LIKE', '%"CreditCardTransactionStatus":"Captured"%')
                  ->orWhere('response_json', 'LIKE', '%"CreditCardTransactionStatus":"AuthorizedPendingCapture"%');
        };

        return $this->hasOne(Transaction::class, 'order_id', 'id')
                    ->where(function ($query) use ($mundipaggQuery) {
                        return $query->orWhere($mundipaggQuery);
                    })
                    ->where('type', TransactionType::CARTAO)
                    ->orderBy('id', 'desc');
    }

    public function creditCardTransactions()
    {
        return $this->hasMany(Transaction::class, 'order_id')
                    ->where('type', TransactionType::CARTAO);
    }

    public function lastRefundedCreditcardTransaction()
    {
        return $this->hasOne(Transaction::class, 'order_id', 'id')
                    ->where('type', '=', TransactionType::CARTAO)
                    ->where('response_json', 'LIKE', '%"CreditCardTransactionStatus":"Refunded"%')
                    ->orWhere('response_json', 'LIKE', '%"CreditCardTransactionStatus":"Canceled"%')
                    ->orWhere('response_json', 'LIKE', '%"CreditCardTransactionStatus":"Voided"%')
                    ->orderBy('id', 'desc')->first();
    }

    public function canceledCreditCardTransactions()
    {
        return $this->hasMany(Transaction::class, 'order_id')
                    ->where('type', TransactionType::CARTAO)
                    ->where('response_json', 'LIKE', '%"CreditCardTransactionStatus":"Canceled"%');
    }

    public function lastValidCreditCardPayment()
    {
        $transaction = $this->validCreditCardTransaction;

        if ($transaction) {
            return new PaymentResponse(
                true,
                "",
                "",
                $transaction,
                $transaction->request_json,
                $transaction->response_json
            );
        }

        return null;
    }

    public function lastCreditCardTransaction()
    {
        return $this->hasOne(Transaction::class, 'order_id', 'id')
                    ->where('type', TransactionType::CARTAO)
                    ->orderBy('id', 'desc');
    }

    public function lastBoletoTransaction()
    {
        return $this->hasOne(Transaction::class, 'order_id', 'id')
                    ->where('type', TransactionType::BOLETO)
                    ->orderBy('id', 'desc');
    }

    public function lastPagSeguroTransaction()
    {
        return $this->hasOne(Transaction::class, 'order_id', 'id')
                    ->where('type', TransactionType::BOLETO)
                    ->orderBy('id', 'desc');
    }

    public function upsellOrder()
    {
        return $this->belongsTo(Order::class, "upsell_order_id");
    }

    public function upsellOrders()
    {
        return $this->hasMany(Order::class, "upsell_order_id", "id");
    }

    public function approvedUpsellOrders()
    {
        return $this->hasMany(Order::class, "upsell_order_id", "id")
                    ->whereIn("status", OrderStatus::approved());
    }

    public function getSubTotalAttribute()
    {
        return $this->total + $this->freight_value + $this->interest - $this->discount;
    }

    public function getIsApprovedAttribute()
    {
        return in_array($this->status, OrderStatus::approved());
    }

    public function isPaid()
    {
        return $this->getIsApprovedAttribute();
    }

    public function isCreditCard()
    {
        return mb_strtoupper($this->payment_type_collection) == "CREDITCARD";
    }

    public function isBoleto()
    {
        return mb_strtoupper($this->payment_type_collection) == "BOLETO";
    }

    public function isPagSeguro()
    {
        return mb_strtoupper($this->payment_type_collection) == "PAGSEGURO";
    }

    public function lastAnalyzeStatus()
    {
        $analyzes = $this->analyzes->sortByDesc("batch")->groupBy("batch")->first();

        if ($analyzes) {
            return $analyzes->where("status", false)->count() == 0;
        }
        return true;
    }

    public function hasIPI()
    {
        foreach ($this->itemsProduct as $item) {
            if($item->product && $item->product->ipi > 0){
                return true;
            }
        }

        foreach ($this->itemsBundle as $item) {
            foreach ($item->bundle->products as $product) {
                if ($product->ipi > 0) {
                    return true;
                }
            }
        }

        return false;
    }

    public function hasApp()
    {
        foreach ($this->itemsProduct as $item) {
            if ($item->product->is_app) {
                return true;
            }
        }

        foreach ($this->itemsBundle as $item) {
            foreach ($item->bundle->products as $product) {
                if ($product->is_app) {
                    return true;
                }
            }
        }

        return false;
    }
}
