<?php

namespace App\Http\Controllers\Admin;

use App\Domain\FormFields\MonetaryField;
use App\Domain\FormFields\SelectField;
use App\Domain\FormFields\TextField;
use App\Entities\SalesCommission;
use App\Entities\User;
use App\Http\Requests;
use App\Support\SiteSettings;
use App\Http\Requests\Admin\Request;

class SalesCommissionController extends CrudController
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
        $request = app(Request::class);

        $affiliates = User::whereNotNull("affiliate_id")->lists("name", "id");

        $affiliates = collect([""=>trans("validation.attributes.select")])
            ->union($affiliates);

        $this->dataShared["affiliates"] = $affiliates->toArray();
        $this->dataShared["status"]     = [
            ""                                  => trans("validation.attributes.select"),
            SalesCommission::STATUS_APPROVED    => trans("status.approved"),
            SalesCommission::STATUS_PENDING     => trans("status.pending"),
            SalesCommission::STATUS_SHAVED      => trans("status.shaved"),
            SalesCommission::STATUS_PAID        => trans("status.paid"),
        ];

        $searchFields = [];

        $searchFields["user_id"]    = $request->get("affiliate") ?: $affiliates->flip()->first();
        $searchFields["created_at"] = $this->getDateInterval($request);
        if ($status = $request->get("status")) {
            $searchFields["status"]     = $status;
        }
        $this->model = $this->model->search($searchFields);

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

    public function getFields()
    {
        $status = [
            SalesCommission::STATUS_APPROVED    => trans("status.approved"),
            SalesCommission::STATUS_PENDING     => trans("status.pending"),
            SalesCommission::STATUS_SHAVED      => trans("status.shaved"),
            SalesCommission::STATUS_PAID        => trans("status.paid"),
        ];

        $affiliates = User::whereNotNull("affiliate_id")->lists("name", "id");

        return [
            [
                new TextField("order_id"),
                new SelectField("user_id", $affiliates->toArray())
            ],
            [
                new MonetaryField("value"),
                new SelectField("status", $status),
            ]
        ];
    }
}
