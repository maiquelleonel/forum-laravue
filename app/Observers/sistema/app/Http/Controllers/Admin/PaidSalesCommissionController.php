<?php

namespace App\Http\Controllers\Admin;

use App\Entities\SalesCommission;
use App\Entities\SalesCommissionPaid;
use App\Entities\User;
use App\Http\Requests;
use App\Support\SiteSettings;
use App\Http\Requests\Admin\Request;

class PaidSalesCommissionController extends CrudController
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
     * @var int
     */
    protected $register_per_page = 1000;

    /**
     * @var bool
     */
    protected $createAction = false;

    /**
     * @var string
     */
    protected $modelName = "paid_commission";

    /**
     * UpsellController constructor.
     * @param SiteSettings $siteSettings
     * @param SalesCommissionPaid $model
     */
    public function __construct(SiteSettings $siteSettings, SalesCommissionPaid $model)
    {
        parent::__construct($siteSettings, $model);
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $request = app(Request::class);

        $affiliates = User::whereNotNull("affiliate_id")->lists("name", "id");

        $affiliates = collect([""=>trans("validation.attributes.select")])
                            ->union($affiliates);

        $this->dataShared["affiliates"] = $affiliates->toArray();

        $searchFields = [];
        $searchFields["created_at"] = $this->getDateInterval($request);
        if( $affiliate_id = $request->get("affiliate")){
            $searchFields["user_id"] = $affiliate_id;
        }

        $this->model = $this->model
                            ->search($searchFields);

        return parent::index();
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $request = app(Request::class);

        $this->dataShared["commissions"] = SalesCommission::whereIn("id", $request->get("ids"))->get();
        $this->dataShared["affiliate"]   = User::find($request->get("user_id"));

        return parent::create();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $paid = SalesCommissionPaid::create($request->all());
        SalesCommission::whereIn("id", $request->get("commissions"))
                            ->where("status", SalesCommission::STATUS_APPROVED)
                            ->update([
                                "status"    => SalesCommission::STATUS_PAID,
                                "paid_at"   => $paid->created_at,
                                "sales_commission_paid_id"=> $paid->id
                            ]);
        return redirect()->action( get_class( $this ) . "@index" );
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return [
            "date" => function($model){
                return $model->created_at->format("d/m/Y H:i:s");
            },
            "affiliate" => function($model){
                return $model->user->name;
            },
            "payment_receipt" => function($model){
                return link_to(asset($model->payment_receipt), "DOWNLOAD", ['target'=>'_blank']);
            }
        ];
    }
}
