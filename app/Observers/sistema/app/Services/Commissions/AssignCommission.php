<?php
/**
 * Created by PhpStorm.
 * User: dlima
 * Date: 12/11/17
 * Time: 12:59
 */

namespace App\Services\Commissions;


use App\Entities\ConfigCommissionRule;
use App\Entities\Order;
use App\Entities\PageVisit;
use App\Entities\SalesCommission;
use App\Entities\User;
use App\Repositories\Currency\CurrencyRepository;

class AssignCommission
{
    /**
     * @var CurrencyRepository
     */
    private $currencyRepository;
    /**
     * @var Shaving
     */
    private $shaving;

    /**
     * AssignCommission constructor.
     * @param CurrencyRepository $currencyRepository
     * @param Shaving $shaving
     */
    public function __construct(CurrencyRepository $currencyRepository, Shaving $shaving)
    {
        $this->currencyRepository = $currencyRepository;
        $this->shaving = $shaving;
    }

    /**
     * @param Order $order
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function assign(Order $order)
    {
        if($pageVisit = $order->visit){
            if($affiliate = $this->getAffiliate($pageVisit)){
                if($this->canAssign($order, $affiliate)){
                    if($rule = $this->getCommissionRule($order, $affiliate)){

                        list($commissionValue, $currency) = $this->getCommissionValue($order, $rule);

                        $status = $this->shaving->shouldAssign($order, $affiliate, $rule, $commissionValue, $currency);

                        return $commission = $affiliate->commissions()->create([
                            "order_id"  => $order->id,
                            "value"     => $commissionValue,
                            "status"    => $status ? SalesCommission::STATUS_APPROVED : SalesCommission::STATUS_SHAVED,
                            "currency_id"       =>$rule->currency->id,
                            "conversion_rate"   =>$rule->currency->conversion_rate,
                        ]);
                    }
                }
            }
        }
        return null;
    }

    public function update(Order $order)
    {
        /**
         * @var $commission SalesCommission
         */
        foreach($order->commissions as $commission){
            if($rule = $this->getCommissionRule($order, $commission->user)){
                list($value, $currency) = $this->getCommissionValue($order, $rule);
                $status = $commission->status != SalesCommission::STATUS_SHAVED
                            ? SalesCommission::STATUS_APPROVED
                            : $commission->status;

                $currency = $this->currencyRepository->findByCode($currency);

                $commission->update([
                    "value"             => $value,
                    "status"            => $status,
                    "currency_id"       => $currency->id,
                    "conversion_rate"   => $currency->conversion_rate,
                ]);
            }
        }

        return $order->commissions;
    }

    /**
     * @param PageVisit $pageVisit
     * @return User|null
     */
    public function getAffiliate(PageVisit $pageVisit)
    {
        if (isset($pageVisit->custom_var_v1)) {
            return User::where("affiliate_id", $pageVisit->custom_var_v1)->first();
        } else if (isset($pageVisit->affiliate_id)) {
            return User::where("affiliate_id", $pageVisit->affiliate_id)->first();
        }

        return null;
    }

    /**
     * @param Order $order
     * @param User $affiliate
     * @return ConfigCommissionRule|null
     */
    public function getCommissionRule(Order $order, User $affiliate)
    {
        if ($group = $affiliate->groupCommission) {
            foreach ($group->rules as $rule) {
                $inSite         = in_array( $order->customer->site_id, $rule->sites->lists("id")->toArray() );
                $inOrigin       = in_array( $order->origin, $rule->origins->lists("value")->toArray() );
                $inPaymentType  = in_array( $order->payment_type_collection, $rule->paymentTypes->lists("value")->toArray() );

                if ($inSite && $inOrigin && $inPaymentType) {
                    return $rule;
                }
            }
        }

        return null;
    }

    /**
     * @param Order $order
     * @param ConfigCommissionRule $rule
     * @return array value|currency
     */
    public function getCommissionValue(Order $order, ConfigCommissionRule $rule)
    {
        if ($rule->type == ConfigCommissionRule::TYPE_FIXED) {
            return [(float) $rule->value, $rule->currency->code];
        }

        $totalOrder         = $order->total + $order->freight_value - $order->discount;
        $totalCommissionBRL = (float) number_format(($totalOrder)*$rule->value/100, 2, '.', '');

        return [
            $this->currencyRepository->convert($totalCommissionBRL, "BRL", $rule->currency->code),
            $rule->currency->code
        ];
    }

    /**
     * @param Order $order
     * @param User $affiliate
     * @return bool
     */
    public function canAssign(Order $order, User $affiliate)
    {
        return $order->commissions()->where("user_id", $affiliate->id)->count() === 0;
    }
}