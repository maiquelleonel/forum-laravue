<?php

namespace App\Http\Controllers\Admin;

use App\Domain\FormFields\EmailField;
use App\Domain\FormFields\ImageUploadField;
use App\Domain\FormFields\TelephoneField;
use App\Domain\FormFields\TextField;
use App\Http\Requests;
use App\Entities\Company;
use App\Support\SiteSettings;

class CompanyController extends CrudController
{
    /**
     * SiteController constructor.
     * @param SiteSettings $siteSettings
     * @param Company $model
     */
    public function __construct(SiteSettings $siteSettings, Company $model)
    {
        parent::__construct($siteSettings, $model);
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return [
            [
                new TextField("name"),
                [ new TelephoneField("phone"), new EmailField("email") ],
                new TextField("cnpj")
            ],
            [
                new ImageUploadField("logo"),
            ]
        ];
    }

    public function getColumns()
    {
        return [
            "name",
            "phone",
            "cnpj",
            "email",
            "logo" => function( $model ){
                return \Html::image( $model->logo, null, ["style"=>"max-height:30px;"] );
            }
        ];
    }
}
