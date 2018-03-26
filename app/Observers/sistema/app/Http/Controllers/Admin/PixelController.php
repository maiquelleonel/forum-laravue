<?php

namespace App\Http\Controllers\Admin;

use App\Domain\FormFields\TextAreaField;
use App\Domain\FormFields\TextField;
use App\Http\Requests;
use App\Entities\Pixels;
use App\Support\SiteSettings;

class PixelController extends CrudController
{

    /**
     * SiteController constructor.
     * @param SiteSettings $siteSettings
     * @param Pixels $model
     */
    public function __construct(SiteSettings $siteSettings, Pixels $model)
    {
        parent::__construct($siteSettings, $model);
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return [
            new TextField("name"),
            new TextAreaField("page_home"),
            new TextAreaField("page_bundles"),
            new TextAreaField("page_preorder"),
            new TextAreaField("page_checkout"),
            new TextAreaField("page_checkout_retry"),
            new TextAreaField("page_upsell"),
            new TextAreaField("page_additional"),
            new TextAreaField("page_promoexit"),
            new TextAreaField("page_retargeting"),
            new TextAreaField("page_success_creditcard"),
            new TextAreaField("page_success_boleto"),
            new TextAreaField("page_success_pagseguro")
        ];
    }

    public function getColumns()
    {
        return [
            "name",
            "Em uso por" => function ($pixel) {
                return $pixel->sites->implode("name", ", ") ?: "Nenhum site";
            }
        ];
    }
}
