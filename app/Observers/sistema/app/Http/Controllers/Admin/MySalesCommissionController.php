<?php

namespace App\Http\Controllers\Admin;

use App\Entities\SalesCommission;
use App\Http\Requests;
use App\Http\Requests\Admin\Request;
use App\Support\SiteSettings;

class MySalesCommissionController extends CrudController
{
    /**
     * @var bool
     */
    protected $showId = false;

    /**
     * @var string
     */
    protected $sortDirection = "DESC";

    /**
     * @var array
     */
    protected $eagerLoading = ["currency", "order"];

    /**
     * UpsellController constructor.
     * @param SiteSettings $siteSettings
     * @param SalesCommission $model
     */
    public function __construct(SiteSettings $siteSettings, SalesCommission $model)
    {
        parent::__construct($siteSettings, $model);
    }

    public function index()
    {
        $this->modelName = "my_sales_commission";

        list($from, $to) = $this->getDateInterval(app(Request::class));

        $searchBy = [
            "created_at" => [$from, $to]
        ];

        $this->model = $this->model
                            ->search($searchBy)
                            ->where("user_id", auth()->user()->id)
                            ->where("status", "!=", SalesCommission::STATUS_SHAVED);

        return parent::index();
    }

    public function getColumns()
    {
        return [
            "status"=> function ($model) {
                return $model->present()->status;
            },
            "date" => function ($model) {
                return $model->created_at->format("d/m/Y H:i:s");
            },
            "customer" => function ($model) {
                return $model->order->customer->fullName();
            },
            "offer" => function ($model) {
                return $model->order->customer->site->name;
            },
            "utm_source" => function ($model) {
                return $model->order->visit ? $model->order->visit->utm_source : "";
            },
            "utm_campaign" => function ($model) {
                return $model->order->visit ? $model->order->visit->utm_campaign : "";
            },
            "value" => function ($model) {
                return monetary_format($model->value, $model->currency->code) . " " .
                $model->order->present()->paymentTypeIcon;
            },

        ];
    }
}
