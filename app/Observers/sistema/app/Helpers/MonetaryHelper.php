<?php

if(!function_exists("calculate_installments")){
    /**
     * Calculate installments
     * @param $totalValue
     * @param int $maxInstallments
     * @param int $interest
     * @param bool $compoundInterest
     * @return array
     * @throws Exception
     */
    function calculate_installments($totalValue, $maxInstallments = 12, $interest = 0, $compoundInterest = true)
    {
        if ($maxInstallments < 1 OR $maxInstallments > 12) {
            throw new \Exception('Invalid installments, min 1, max 12');
        }

        $installments = [];

        $interest = $interest > 0 ? $interest/100 : 0;


        // Juros Composto
        if ($interest > 0 && $compoundInterest) {
            foreach(range(1, $maxInstallments) as $installment) {
                $installmentValue = $totalValue * pow((1 + $interest), $installment-1);
                $installments[$installment] = (float) number_format($installmentValue, 2, ".", "");
            }

            return $installments;
        }

        // Juros simples ou sem juros
        foreach(range(1, $maxInstallments) as $installment) {
            $installmentValue = ($totalValue) + ($installment>1?($totalValue *$interest):0);
            $installments[$installment] = (float) number_format($installmentValue, 2, ".", "");
        }

        return $installments;
    }
}

if(!function_exists("displayInstallments")){
    /**
     * Generate installments with money format
     * @param $totalValue
     * @param int $maxInstallments
     * @param int $interest
     * @param bool $compoundInterest
     * @return array
     * @throws Exception
     */
    function displayInstallments($totalValue, $maxInstallments = 12, $interest = 0, $compoundInterest = true, $showTotal = false)
    {
        $installments = calculate_installments($totalValue, $maxInstallments, $interest, $compoundInterest);

        $pattern = "%s x de %s";
        if ($showTotal) {
            $pattern.= " (%s)";
        }

        foreach ($installments as $installment => &$value) {
            /*if ($interest > 0 && $installment == 2) {
                $pattern.= " *";
            }*/
            $total = number_format($value/$installment, 2, ".", "");
            $value = number_format($value, 2, ".", "");
            $value = sprintf($pattern, $installment, monetary_format($total), monetary_format($value));
        }

        return $installments;
    }
}

if(!function_exists("monetary_format")){
    /**
     * Format to monetary style
     * @param float $value
     * @param string $currency
     * @return string
     */
    function monetary_format($value, $currency = "BRL")
    {
        $currency = \Cache::remember("currency_{$currency}", 60, function() use ($currency){
            $currency = \App\Entities\Currency::where("code", $currency)->first();

            if (!$currency) {
                $currency = new \App\Entities\Currency;
            }

            return $currency;
        });

        return $currency->prefix . number_format(
            $value,
            $currency->decimals,
            $currency->decimal,
            $currency->thousand
        ) . $currency->suffix;
    }
}

if(!function_exists("calculate_total_interest")){
    /**
     * Get total value with interest base in installments
     * @param $total
     * @param int $installments
     * @param int $interest
     * @return mixed
     * @throws Exception
     */
    function calculate_total_interest($total, $installments = 12, $interest = 0)
    {
        $totalInstallments = calculate_installments($total, $installments, $interest);

        return $totalInstallments[ $installments ];
    }
}

if(!function_exists("installments")){
    /**
     * Display installments by total value, like "10 x de R$ 29,90"
     * @param $total
     * @param $installments
     * @return string
     */
    function installments($total, $installments)
    {
        if ($installments == 1) {
            return monetary_format($total);
        }

        if ($installments < 1) {
            $installments = 1;
        }

        return "$installments x " . monetary_format($total/$installments);
    }
}
