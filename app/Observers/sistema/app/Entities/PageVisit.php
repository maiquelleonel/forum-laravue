<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * @SWG\Definition(
 *     definition="Tracking",
 *     @SWG\Property(property="utm_source", type="string", description="Traffic Source", example="facebook"),
 *     @SWG\Property(property="utm_medium", type="string", description="Campaign Medium", example="cpc"),
 *     @SWG\Property(property="utm_campaign", type="string", description="Campaign Name", example="test_footer"),
 *     @SWG\Property(property="utm_term", type="string", description="Paid Keywords", example="a/b"),
 *     @SWG\Property(property="utm_content", type="string", description="Campaign Content", example="foo-bar"),
 *     @SWG\Property(property="referrer", type="string", description="Page Referrer", example="https://google.com/"),
 *     @SWG\Property(property="click_id", type="string", description="Click ID", example="x1b2es2"),
 *     @SWG\Property(property="custom_var_k1", type="string", description="Custom Var Key 1", example="a"),
 *     @SWG\Property(property="custom_var_v1", type="string", description="Custom Var Value 1", example="502"),
 *     @SWG\Property(property="custom_var_k2", type="string", description="Custom Var Key 2", example="pub_id"),
 *     @SWG\Property(property="custom_var_v2", type="string", description="Custom Var Value 2", example="123"),
 *     @SWG\Property(property="custom_var_k3", type="string", description="Custom Var Key 3", example="s1"),
 *     @SWG\Property(property="custom_var_v3", type="string", description="Custom Var Value 3", example="a1b2c3"),
 *     @SWG\Property(property="custom_var_k4", type="string", description="Custom Var Key 4", example="s2"),
 *     @SWG\Property(property="custom_var_v4", type="string", description="Custom Var Value 4", example="a1b2c3"),
 *     @SWG\Property(property="custom_var_k5", type="string", description="Custom Var Key 5", example="s3"),
 *     @SWG\Property(property="custom_var_v5", type="string", description="Custom Var Value 5", example="a1b2c3")
 * )
 * @property User affiliate
 */
class PageVisit extends Model
{
    protected $table = 'page_visit';

    protected $fillable = [
        "visitor_id",
        "customer_id",

        "utm_source",
        "utm_medium",
        "utm_campaign",
        "utm_term",
        "utm_content",

        "referrer",

        "custom_var_k1",
        "custom_var_v1",
        "custom_var_k2",
        "custom_var_v2",
        "custom_var_k3",
        "custom_var_v3",
        "custom_var_k4",
        "custom_var_v4",
        "custom_var_k5",
        "custom_var_v5",

        "click_id",

        "created_at"
    ];

    public function pageViews()
    {
        return $this->hasMany(PageVisitUrl::class, "page_visit_id");
    }

    public function entryPage()
    {
        return $this->hasOne(PageVisitUrl::class, "page_visit_id");
    }

    public function scopeGetSources($query)
    {
        return $this->getDefaultList($query, 'utm_source');
    }

    public function scopeGetCampaigns($query, $media=null)
    {
        if ($media) {
            $query = $query->where("utm_content", $media);
        }
        return $this->getDefaultList($query, 'utm_campaign');
    }

    public function scopeGetMedias($query)
    {
        return $this->getDefaultList($query, 'utm_content');
    }

    public function scopeGetKeywords($query)
    {
        return $this->getDefaultList($query, 'utm_term');
    }

    private function getDefaultList($query, $fieldName)
    {
        return $query
                ->whereNotNull($fieldName)
                ->groupBy($fieldName)
                ->orderBy($fieldName)
                ->lists($fieldName);
    }

    public function affiliate()
    {
        return $this->belongsTo(User::class, "custom_var_v1", "affiliate_id");
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, "customer_id");
    }

    public function orders()
    {
        return $this->hasMany(Order::class, "page_visit_id");
    }
}