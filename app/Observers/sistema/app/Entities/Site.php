<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laracasts\Presenter\PresentableTrait;
use App\Presenters\SitePresenter;

/**
 * @property integer id
 */
class Site extends Model
{
    use PresentableTrait;

    protected $table = "site";

    public $presenter = SitePresenter::class;

    protected $fillable = [
        "name",
        "domain",
        "remarketing_domain",
        "color",
        "theme",
        "view_folder",
        "payment_setting_id",
        "payment_setting_callcenter_id",
        "pixels_id",
        "company_id",
        "email_campaign_setting_id",
        "erp_setting_id",
        "path_version",
        "bundle_group_id",
        "gtm_code",
        "auto_refund",
        "domain_must_redirect_to_rt",
        "show_cronometer",
        "show_in_stock_message"
    ];

    public function paymentSetting()
    {
        return $this->belongsTo(PaymentSetting::class, "payment_setting_id");
    }

    public function callCenterPaymentSetting()
    {
        return $this->belongsTo(PaymentSetting::class, "payment_setting_callcenter_id");
    }

    public function pixel()
    {
        return $this->belongsTo(Pixels::class, "pixels_id");
    }

    public function company()
    {
        return $this->belongsTo(Company::class, "company_id");
    }

    public function emailCampaignSetting()
    {
        return $this->belongsTo(EmailCampaignSetting::class, "email_campaign_setting_id");
    }

    public function erpSetting()
    {
        return $this->belongsTo(ErpSetting::class, "erp_setting_id");
    }

    public function bundles()
    {
        return $this->hasMany(Bundle::class, "bundle_group_id", "bundle_group_id");
    }

    public function getMainProductAttribute()
    {
        return \Cache::remember("main_product_" . $this->id, 30, function () {
            $bundles = $this->bundles()->with("products.bundles")->get();
            if ($bundles->count()) {
                $products = [];
                foreach ($bundles as $bundle) {
                    foreach ($bundle->products as $product) {
                        if (!isset($products[$product->id])) {
                            $products[$product->id] = (Object)[
                                "product" => $product,
                                "qty"     => $product->pivot->product_qty
                            ];
                        } else {
                            $products[$product->id]->qty += $product->pivot->product_qty;
                        }
                    }
                }
                if (count($products)) {
                    return collect($products)->sortByDesc("qty")->first()->product;
                }
            }
        });
    }

    public function bundleGroup()
    {
        return $this->belongsTo(BundleGroup::class, "bundle_group_id");
    }

    public function upsells()
    {
        return $this->hasManyThrough(
            Upsell::class,
            Bundle::class,
            "bundle_group_id",
            "from_bundle_id",
            "bundle_group_id"
        );
    }

    public function additionals()
    {
        return $this->hasManyThrough(
            Additional::class,
            Bundle::class,
            "bundle_group_id",
            "from_bundle_id",
            "bundle_group_id"
        );
    }

    public function externalServices()
    {
        return $this->belongsToMany(ExternalServiceSettings::class, 'external_service_settings_site');
    }

    public function slackNotifications()
    {
        return $this->belongsToMany(ExternalServiceSettings::class, 'external_service_settings_site')
                    ->where([
                        ["service", '=', "Slack"],
                        ['name','like','%Notificações de Erro%']
                    ]);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function leads()
    {
        $hasMany = $this->hasMany(Customer::class);

        if ($this->affiliate_id) {
            return $hasMany
                    ->join("page_visit", "page_visit.customer_id", "=", "customers.id")
                    ->where("page_visit.custom_var_v1", $this->affiliate_id);
        }

        return $hasMany;
    }

    public function commissions()
    {
        $query = SalesCommission::query()
                ->select("sales_commission.*", "customers.site_id")
                ->join("orders", "orders.id", "=", "sales_commission.order_id")
                ->join("customers", "customers.id", "=", "orders.customer_id")
                ->where("sales_commission.status", "!=", SalesCommission::STATUS_SHAVED)
                ->where(function ($query) {
                    if ($this->user_id) {
                        $query->where("sales_commission.user_id", $this->user_id);
                    }
                });

        return new HasMany($query, $this, 'customers.site_id', 'id');
    }

    public function pixels()
    {
        return $this->hasMany(AffiliatePixel::class, "site_id");
    }
}
