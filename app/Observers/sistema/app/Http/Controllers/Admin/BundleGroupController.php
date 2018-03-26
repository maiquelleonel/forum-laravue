<?php

namespace App\Http\Controllers\Admin;

use App\Domain\FormFields\ImageUploadField;
use App\Domain\FormFields\TextAreaField;
use App\Domain\FormFields\TextField;
use App\Entities\BundleGroup;
use App\Http\Requests;
use App\Support\SiteSettings;

class BundleGroupController extends CrudController
{
    /**
     * SiteController constructor.
     * @param SiteSettings $siteSettings
     * @param BundleGroup $model
     */
    public function __construct(SiteSettings $siteSettings, BundleGroup $model)
    {
        parent::__construct($siteSettings, $model);
    }

    public function getColumns()
    {
        return [
            "image" => function ($model) {
                if ($model->image) {
                    return \Html::image($model->image, null, ['style'=>'max-height:50px']);
                }
            },
            "name",
            "description"
        ];
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return [
            [
                new TextField("name"),
                [
                    new TextAreaField("description"),
                    new ImageUploadField("image")
                ]
            ]
        ];
    }
}
