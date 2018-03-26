<?php
/**
 * Created by PhpStorm.
 * User: dlima
 * Date: 1/22/18
 * Time: 10:48
 */

namespace App\Services\Commissions;


use App\Entities\ConfigCommissionRule;
use App\Entities\ConfigCommissionRuleOrigin;
use App\Entities\ConfigCommissionRulePaymentType;
use App\Entities\Order;
use App\Entities\ShavingCounter;
use App\Entities\User;
use App\Repositories\Currency\CurrencyRepository;

class Shaving
{
    /**
     * @var CurrencyRepository
     */
    private $currencyRepository;

    /**
     * AssignCommission constructor.
     * @param CurrencyRepository $currencyRepository
     */
    public function __construct(CurrencyRepository $currencyRepository)
    {
        $this->currencyRepository = $currencyRepository;
    }

    /**
     * @param Order $order
     * @param User $affiliate
     * @param ConfigCommissionRule $rule
     * @param float $commissionValue
     * @param $currencyCode
     * @param bool $saveCounters
     * @return bool
     */
    public function shouldAssign(Order $order, User $affiliate, ConfigCommissionRule $rule, $commissionValue, $currencyCode, $saveCounters = true)
    {
        if ($rule->shaving_rate > 0 && $rule->shaving_rate < 100) {

            $origin = $this->extractOrigin($rule, $order);

            $paymentType = $this->extractPaymentType($rule, $order);

            $shavingCounter = $this->getShavingCounter($affiliate, $order, $origin, $paymentType);

            $isFirst = $shavingCounter->isFirst();

            $shavingCounter->orders_qty++;

            if($rule->type == ConfigCommissionRule::TYPE_FIXED){
                $rate = $this->getFixedShaveRate($order, $rule, $shavingCounter, $currencyCode);
            } else {
                $rate = $this->getPercentageShaveRate($shavingCounter);
            }

            $shavingCounter->orders_amount+=($order->total+$order->freight_value-$order->discount);

            if(($rate <= $rule->shaving_rate) || $isFirst){
                if($saveCounters){
                    $shavingCounter->commissions_paid_qty++;
                    $commissionValue = $this->currencyRepository->convert($commissionValue, $currencyCode, "BRL");
                    $shavingCounter->commissions_paid_amount+= $commissionValue;
                    $shavingCounter->save();
                }
                return true;
            }

            if($saveCounters){
                $shavingCounter->save();
            }
            return false;
        }

        return true;
    }

    /**
     * @param Order $order
     * @param ConfigCommissionRule $rule
     * @param ShavingCounter $shavingCounter
     * @param $currencyCode
     * @return float
     */
    public function getFixedShaveRate(Order $order, ConfigCommissionRule $rule, ShavingCounter $shavingCounter, $currencyCode)
    {
        // ( Valor Comissões Pagas + Valor possivel commissao ) / ( Valor Pedidos + Valor Pedido Atual )
        $value = $this->currencyRepository->convert($rule->value, $currencyCode, "BRL");
        $paidCommissions = $shavingCounter->commissions_paid_amount + $value;
        $orders = $shavingCounter->orders_amount + $order->total + $order->freight_value - $order->discount;

        return number_format(($paidCommissions / $orders)*100, 2, '.', '');
    }

    /**
     * @param ShavingCounter $shavingCounter
     * @return float
     */
    public function getPercentageShaveRate(ShavingCounter $shavingCounter)
    {
        if($shavingCounter->orders_qty <= 0){
            return 0;
        }
        return number_format((($shavingCounter->commissions_paid_qty+1) / $shavingCounter->orders_qty) * 100, 2, '.', '');
    }

    /**
     * @param ConfigCommissionRule $rule
     * @param Order $order
     * @return mixed
     */
    private function extractOrigin($rule, $order)
    {
        return $rule->origins->filter(function($origin) use ($order){
            return $origin->value === $order->origin;
        })->first();
    }

    /**
     * @param $rule
     * @param $order
     * @return mixed
     */
    private function extractPaymentType($rule, $order)
    {
        return $rule->paymentTypes->filter(function($paymentType) use ($order){
            return $paymentType->value === $order->payment_type_collection;
        })->first();
    }

    /**
     * @param User $affiliate
     * @param Order $order
     * @param ConfigCommissionRuleOrigin $origin
     * @param ConfigCommissionRulePaymentType $paymentType
     * @return ShavingCounter
     */
    private function getShavingCounter($affiliate, $order, $origin, $paymentType)
    {
        return ShavingCounter::firstOrCreate([
            "site_id" => $order->customer->site_id,
            "user_id" => $affiliate->id,
            "config_commission_rule_origin_id" => $origin->id,
            "config_commission_rule_payment_type_id" => $paymentType->id
        ]);
    }
}