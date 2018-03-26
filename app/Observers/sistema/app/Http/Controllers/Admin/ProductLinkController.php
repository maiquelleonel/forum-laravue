<?php

namespace App\Http\Controllers\Admin;

use App\Domain\FormFields\SelectField;
use App\Domain\FormFields\TextField;
use App\Entities\Product;
use App\Entities\ProductLink;
use App\Http\Requests;
use App\Support\SiteSettings;

class ProductLinkController extends CrudController
{
    /**
     * SiteController constructor.
     * @param SiteSettings $siteSettings
     * @param ProductLink $model
     */
    public function __construct(SiteSettings $siteSettings, ProductLink $model)
    {
        parent::__construct($siteSettings, $model);
    }

    /**
     * @return array
     */
    public function getFields()
    {
        $products = Product::orderBy('name', 'asc')->lists('name', 'id');

        return [
            [
                new SelectField("product_id", $products),
                new TextField("url")
            ],
            [
                new TextField("name"),
                new SelectField("is_public", [
                    true    => 'Sim',
                    false   => 'Não'
                ])
            ]
        ];
    }

    public function getColumns()
    {
        return [
            "product_id" => function ($model) {
                return $model->product->name;
            },
            "name",

            // @todo Conflict with UrlGenerator helper ( url )
            "url" => function($model){
                return $model->url;
            },

            'is_public' => function($model){
                return $model->is_public ? "Sim" : "Não";
            }
        ];
    }

}
