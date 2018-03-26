<?php
/**
 * Created by PhpStorm.
 * User: dlima
 * Date: 1/17/18
 * Time: 09:44
 */

namespace App\Repositories\Currency;


use App\Entities\Currency;
use App\Repositories\BaseEloquentRepository;

class CurrencyRepository extends BaseEloquentRepository
{

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Currency::class;
    }

    /**
     * @param $code
     * @return Currency
     */
    public function findByCode($code)
    {
        return $this->findByField("code", $code)->first();
    }

    /**
     * @return array
     */
    public function toSelectArray()
    {
        $list       = [];
        foreach($this->all() as $currency){
            $list[$currency->id] = "{$currency->code} | {$currency->name}";
        }
        return $list;
    }

    /**
     * @param $amountInUSD
     * @param $conversionRate
     * @return float
     */
    public function convertToReal($amountInUSD, $conversionRate)
    {
        return $amountInUSD / $conversionRate;
    }

    /**
     * @param $amountInBRL
     * @param string $to
     * @return float
     */
    public function convertTo($amountInBRL, $to = "USD")
    {
        $to     = $this->findByField("code", $to)->first();
        return $amountInBRL * $to->conversion_rate;
    }

    /**
     * @param $amount
     * @param string $from
     * @param string $to
     * @return float
     */
    public function convert($amount, $from = "BRL", $to = "USD")
    {
        $from   = $this->findByField("code", $from)->first();
        $to     = $this->findByField("code", $to)->first();
        return $amount * $to->conversion_rate / $from->conversion_rate;
    }
}