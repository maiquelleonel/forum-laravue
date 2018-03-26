<?php

namespace App\Http\Controllers\Admin;

use App\Entities\BundleGroup;
use App\Entities\Upsell;
use App\Http\Requests;
use App\Http\Requests\Admin\UpsellRequest;
use App\Support\SiteSettings;
use App\Http\Requests\Admin\Request;

class UpsellController extends CrudController
{
    protected $deleteAction = true;

    /**
     * UpsellController constructor.
     * @param SiteSettings $siteSettings
     * @param Upsell $model
     */
    public function __construct(SiteSettings $siteSettings, Upsell $model)
    {
        parent::__construct($siteSettings, $model);
    }

    public function index()
    {
        $columns = $this->getColumns();
        $upsellGroups = BundleGroup::with('upsells.fromBundle', 'upsells.toBundle')->has('upsells')->get();
        $header = $this->modelName;
        $editAction = get_class( $this ) . "@edit";
        $createAction = get_class( $this ) . "@create";
        $deleteAction = $this->deleteAction ? get_class( $this ) . "@destroy" : false;
        $showId = $this->showId;

        return view("admin.pages.upsells.index", compact("upsellGroups", "header", "columns", "editAction", "createAction", "deleteAction", "showId"));
    }

    /**
     * @inheritdoc
     */
    public function edit($id)
    {
        $this->dataShared["groups"] = BundleGroup::all();
        return parent::edit($id);
    }

    /**
     * @inheritdoc
     */
    public function create()
    {
        $this->dataShared["groups"] = BundleGroup::all();
        return parent::create();
    }

    /**
     * @return array columns
     */
    public function getColumns()
    {
        return [
            /**
             * Pacote Origem
             */
            "Pacote Origem"    => function ($upsell) {
                return
                    \Html::strong($upsell->fromBundle->name) .
                    \Html::uList( $upsell->fromBundle->products, function($item) {
                        return sprintf("%s X %s", $item->pivot->product_qty, $item->name);
                    });
            },
            function ($upsell) {
                return $upsell->fromBundle->present()->newPrice;
            },
            function ($upsell) {
                return \Html::image(asset($upsell->fromBundle->image), null, ["style"=>"max-height: 50px"]);
            },

            /**
             * Arrow
             */
            function ( $upsell ) {
                return \Html::faIcon("arrow-right", 30);
            },

            /**
             * Pacote Destino
             */
            "Pacote Destino" => function ($upsell) {
                return
                    \Html::strong($upsell->toBundle->name) .
                    \Html::uList( $upsell->toBundle->products, function($item) {
                        return sprintf("%s X %s", $item->pivot->product_qty, $item->name);
                    });
            },
            function ($upsell) {
                return $upsell->toBundle->present()->newPrice;
            },
            function ($upsell) {
                return \Html::image(asset($upsell->toBundle->image), null, ["style"=>"max-height: 50px"]);
            }
        ];
    }

    /**
     * @inheritdoc
     */
    public function store(Request $request)
    {
        $request = app(UpsellRequest::class);
        return parent::store($request);
    }

    /**
     * @inheritdoc
     */
    public function update(Request $request, $upsellId)
    {
        $request = app(UpsellRequest::class);
        return parent::update($request, $upsellId);
    }
}
