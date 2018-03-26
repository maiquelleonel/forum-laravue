<?php

namespace App\Http\Controllers\Admin;

use App\Entities\Additional;
use App\Entities\BundleGroup;
use App\Entities\Product;
use App\Http\Requests;
use App\Http\Requests\Admin\AdditionalRequest;
use App\Support\SiteSettings;
use App\Http\Requests\Admin\Request;

class AdditionalController extends CrudController
{
    protected $deleteAction = true;

    /**
     * UpsellController constructor.
     * @param SiteSettings $siteSettings
     * @param Additional $model
     */
    public function __construct(SiteSettings $siteSettings, Additional $model)
    {
        parent::__construct($siteSettings, $model);
    }

    public function index()
    {
        $columns = $this->getColumns();
        $additionalGroups = BundleGroup::with('additional.fromBundle', 'additional.product')->has('additional')->get();
        $header = $this->modelName;
        $editAction = get_class( $this ) . "@edit";
        $createAction = get_class( $this ) . "@create";
        $deleteAction = $this->deleteAction ? get_class( $this ) . "@destroy" : false;
        $showId = $this->showId;

        return view("admin.pages.additional.index", compact("additionalGroups", "header", "columns", "editAction", "createAction", "deleteAction", "showId"));
    }

    /**
     * @inheritdoc
     */
    public function edit($id)
    {
        $this->dataShared["groups"] = BundleGroup::all();
        $this->dataShared["products"] = Product::all();
        return parent::edit($id);
    }

    /**
     * @inheritdoc
     */
    public function create()
    {
        $this->dataShared["groups"] = BundleGroup::all();
        $this->dataShared["products"] = Product::all();
        return parent::create();
    }

    /**
     * @inheritdoc
     */
    public function update(Request $request, $id)
    {
        $request = app(AdditionalRequest::class);
        return parent::update($request, $id);
    }

    /**
     * @inheritdoc
     */
    public function store(Request $request)
    {
        $request = app(AdditionalRequest::class);
        return parent::store($request);
    }

    public function getColumns()
    {
        return [
            "Pacote Origem"    => function ($additional) {
                return
                    \Html::strong($additional->fromBundle->name) .
                    \Html::uList( $additional->fromBundle->products, function($item) {
                        return sprintf("%s X %s", $item->pivot->product_qty, $item->name);
                    });
            },
            function ($additional) {
                return $additional->fromBundle->present()->newPrice;
            },
            function ($additional) {
                return \Html::image(asset($additional->fromBundle->image), null, ["style"=>"max-height: 50px"]);
            },

            /**
             * Arrow
             */
            function ( $additional ) {
                return \Html::faIcon("arrow-right", 30);
            },
            /**
             * Arrow
             */
            "Ordem" => function ( $additional ) {
                return sprintf("<b style='font-size: 46px; line-height: 40px'>%s</b>", $additional->order);
            },

            "Adicional" => function ($additional) {
                return sprintf("<b>%s</b><br>Até <b>%sx</b> <b>%s</b> à <b>%s</b> cada",
                    $additional->name,
                    $additional->qty_max,
                    $additional->product->name,
                    monetary_format($additional->price)
                );
            },

            function ($additional) {
                return \Html::image(asset($additional->product->image), null, ["style"=>"max-height: 50px"]);
            }
        ];
    }
}
