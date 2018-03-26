<?php
/**
 * Created by PhpStorm.
 * User: dlima
 * Date: 1/22/18
 * Time: 14:31
 */

namespace App\Services\Pixels;


use App\Entities\AffiliatePixel as AffiliatePixelEntity;
use App\Entities\Order;
use App\Entities\SalesCommission;
use App\Entities\Site;
use App\Entities\User;
use App\Services\Commissions\AssignCommission;
use App\Services\Commissions\Shaving;
use App\Services\Tracking\OutputVariableParser;
use App\Services\Tracking\VisitPage;

class AffiliatePixel
{
    /**
     * @var VisitPage
     */
    private $visitPage;
    /**
     * @var Shaving
     */
    private $shaving;
    /**
     * @var AssignCommission
     */
    private $assignCommission;
    /**
     * @var OutputVariableParser
     */
    private $variableParser;

    /**
     * AffiliatePixel constructor.
     * @param VisitPage $visitPage
     * @param Shaving $shaving
     * @param AssignCommission $assignCommission
     * @param OutputVariableParser $variableParser
     */
    public function __construct(VisitPage $visitPage, Shaving $shaving, AssignCommission $assignCommission, OutputVariableParser $variableParser)
    {
        $this->visitPage = $visitPage;
        $this->shaving = $shaving;
        $this->assignCommission = $assignCommission;
        $this->variableParser = $variableParser;
    }

    public function getCurrentHtmlPixels(Site $site, $page)
    {
        $pixels = $this->getCurrentPixels($site, $page);

        $htmlContent = "\n\r<!-- BEGIN AFFILIATE PIXELS -->\n\r";
        $vars = $this->variableParser->getVars($this->visitPage->getCurrentVisit());

        foreach ($pixels as $pixel) {
            $htmlContent.= "\n\r {$this->variableParser->parseString($pixel->code, $vars)} \n\r";
        }

        $htmlContent.= "\n\r<!-- END AFFILIATE PIXELS -->\n\r";

        return $htmlContent;
    }

    /**
     * @param Site $site
     * @param $page
     * @return array
     */
    public function getCurrentPixels(Site $site, $page)
    {
        $visit = $this->visitPage->getCurrentVisit();

        if($affiliate = $visit->affiliate){
            if (session("order_id") && in_array($page, ["page_upsell", "page_additional", "page_success_creditcard", "page_success_boleto", "page_success_pagseguro"])) {
                return $this->getConversionPixels($affiliate, $site, $page, session("order_id"));
            }
            return $this->getPixels($affiliate, $site, $page);
        }

        return [];
    }

    public function getPixels(User $user, Site $site, $page)
    {
        return AffiliatePixelEntity::where([
            "user_id"   => $user->id,
            "site_id"   => $site->id,
            "page"      => $page
        ])->get();
    }

    public function getConversionPixels(User $user, Site $site, $page, $orderId)
    {
        $order = Order::find($orderId);
        $commissions = $order->commissions()->where("user_id", $user->id)->get();

        if($commissions->count() > 0){
            foreach($commissions as $commission){
                if($commission->status == SalesCommission::STATUS_SHAVED){
                    return [];
                }
            }
            return $this->getPixels($user, $site, $page);
        }

        if($rule = $this->assignCommission->getCommissionRule($order, $user)){
            list($commissionValue, $currency)= $this->assignCommission->getCommissionValue($order, $rule);
            if($this->shaving->shouldAssign($order, $user, $rule, $commissionValue, $currency, false)){
                return $this->getPixels($user, $site, $page);
            }
        }

        return [];
    }
}