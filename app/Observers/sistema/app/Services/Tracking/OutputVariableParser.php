<?php
/**
 * Created by PhpStorm.
 * User: Wagner
 * Date: 16/03/2018
 * Time: 16:26
 */

namespace App\Services\Tracking;


use App\Entities\PageVisit;

class OutputVariableParser
{
    /**
     * @param $string
     * @param array $vars
     * @return string
     */
    public function parseString($string, $vars=[])
    {
        foreach($vars as $key=>$value){
            $vars["{".$key."}"] = $value;
            unset($vars[$key]);
        }

        return str_ireplace(array_keys($vars), array_values($vars), $string);
    }

    /**
     * @param PageVisit $pageVisit
     * @return array
     */
    public function getVars(PageVisit $pageVisit)
    {
        $extractedValues = [];

        $utmVars = config("tracking.utm_vars");

        foreach($utmVars as $var){
            $extractedValues[$var] = $pageVisit->{$var};
        }

        foreach(config("tracking.custom_vars") as $key=>$varName){
            if($varName){
                $extractedValues[$varName] = $pageVisit->{config("tracking.custom_var_prefix")."v{$key}"};
            }
        }

        $extractedValues = array_replace($extractedValues, [
            "order_id"      => $pageVisit->orders->last() ? $pageVisit->orders->last()->id : null,
            "customer_id"   => $pageVisit->customer_id,
        ]);

        return $extractedValues;
    }
}