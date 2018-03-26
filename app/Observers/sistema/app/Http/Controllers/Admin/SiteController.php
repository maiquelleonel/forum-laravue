<?php

namespace App\Http\Controllers\Admin;

use App\Domain\FormFields\SelectField;
use App\Domain\FormFields\TextField;
use App\Domain\FormFields\BooleanField;
use App\Entities\BundleGroup;
use App\Entities\EmailCampaignSetting;
use App\Entities\ErpSetting;
use App\Http\Requests;
use Faker\Provider\hu_HU\Text;
use App\Http\Requests\Admin\Request;
use App\Entities\Company;
use App\Entities\PaymentSetting;
use App\Entities\Pixels;
use App\Entities\Site;
use App\Support\SiteSettings;

class SiteController extends CrudController
{
    protected $duplicateAction = true;

    /**
     * SiteController constructor.
     * @param SiteSettings $siteSettings
     * @param Site $model
     */
    public function __construct(SiteSettings $siteSettings, Site $model)
    {
        parent::__construct($siteSettings, $model);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateSession(Request $request)
    {
        \Cookie::queue("sites", $request->get("sites", []), time()+60*60*24*365*10, "/");

        return back();
    }

    /**
     * @return array
     */
    public function getFields()
    {
        $themes = config("themes");

        array_walk($themes, function(&$theme) {
            $theme = $theme["name"];
        });

        $nothing        = collect([''=>trans("validation.attributes.select")]);
        $payments       = PaymentSetting::lists("name", "id");
        $pixels         = Pixels::lists("name", "id");
        $companies      = Company::lists("name", "id");
        $emailCampaigns = EmailCampaignSetting::lists("name", "id");
        $erpSettings    = ErpSetting::lists("name", "id");
        $bundleGroups   = BundleGroup::lists("name", "id");
        $viewsFolder    = $this->getViewsFolder( resource_path("views/sites") );

        return [
            [
                [new TextField("name")                                        , new TextField("path_version")],
                [new TextField("domain")                                      , new TextField("remarketing_domain")],
                [new TextField("color")                                       , new SelectField("theme", $themes)],
                [new SelectField("view_folder", $nothing->union($viewsFolder)), new TextField("gtm_code")],
                [
                    new BooleanField("show_cronometer"),
                    new BooleanField("show_in_stock_message")
                ]
            ],[
                [
                    new SelectField("payment_setting_id", $nothing->union($payments)),
                    new SelectField("payment_setting_callcenter_id", $nothing->union($payments)),
                ],
                [
                    new BooleanField('auto_refund'),
                    new SelectField("pixels_id", $nothing->union($pixels))
                ],
                [
                    new BooleanField('domain_must_redirect_to_rt', ["title"=>"Redireciona caso não tenha variável ?offer=cake na url"]),
                    new SelectField("bundle_group_id", $nothing->union($bundleGroups)),
                    new SelectField("company_id", $nothing->union($companies))
                ],
                new SelectField("email_campaign_setting_id", $nothing->union($emailCampaigns)),
                new SelectField("erp_setting_id", $nothing->union($erpSettings)),
            ]
        ];
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return [
            "name",
            "domain",
            "color",
            "path_version" => function ($model) {
                return $model->path_version ?: "v1";
            },
            "theme" => function ($model) {
                return config("themes.".$model->theme.".name");
            },
            "payment_setting_id" => function ( $model ) {
                return $model->paymentSetting ? $model->paymentSetting->name : "Nenhum";
            },
            "payment_setting_callcenter_id" => function ( $model ) {
                return $model->callCenterpaymentSetting ? $model->callCenterpaymentSetting->name : "Nenhum";
            },
            "company_id" => function ( $model ) {
                return $model->company ? $model->company->name : "Nenhum";
            },
        ];
    }

    /**
     * @param $dir string Initial Path
     * @param array $folders
     * @param string $prefix
     * @param bool $isRoot
     * @return array
     */
    private function getViewsFolder($dir, &$folders = [], $prefix = "", $isRoot = true)
    {
        $ffs = scandir($dir);

        unset($ffs[array_search('.', $ffs, true)]);
        unset($ffs[array_search('..', $ffs, true)]);

        // prevent empty ordered elements
        if (count($ffs) < 1)
            return [];

        foreach($ffs as $ff){
            if(is_dir($dir.'/'.$ff)) {

                if ($prefix) {
                    $folders[$prefix."/".$ff] = $prefix."/".$ff;
                } else {
                    $folders[$ff] = $ff;
                }

                $this->getViewsFolder($dir.'/'.$ff, $folders, $ff);
            }
        }

        if (!$isRoot) {
            return [];
        }

        ksort($folders);
        return $folders;
    }
}
