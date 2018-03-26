<?php

namespace App\Services;

use Carbon\Carbon;
use Khill\Lavacharts\DataTables\DataTable;
use Khill\Lavacharts\DataTables\Formats\NumberFormat;
use Lava;

class ChartService
{
    /**
     * @param $chartName
     * @param array $orders array of Collections
     * @param Carbon $from
     * @param Carbon $to
     * @param string $legend
     * @param array $customData
     * @return mixed
     */
    public function addLineChart($chartName, array $orders, Carbon $from, Carbon $to, $legend = "", $customData = [])
    {
        return Lava::LineChart($chartName, $this->getDataTable($orders, $from, $to), array_merge([
            'title'=> $legend,
        ], $customData, $this->getDefaults()));
    }

    /**
     * @param $chartName
     * @param array $orders
     * @param Carbon $from
     * @param Carbon $to
     * @param string $legend
     * @param array $customData
     * @return mixed
     */
    public function addAreaChart($chartName, array $orders, Carbon $from, Carbon $to, $legend = "", $customData = [])
    {
        return Lava::AreaChart($chartName, $this->getDataTable($orders, $from, $to), array_merge([
            'title'=> $legend,
        ], $customData, $this->getDefaults()));
    }

    /**
     * @param array $objects
     * @param Carbon $from
     * @param Carbon $to
     * @return DataTable
     */
    private function getDataTable(array $objects, Carbon $from, Carbon $to)
    {
        $dataTable = new DataTable;

        $dataTable->addStringColumn('Dia');

        foreach($objects as $key=>$value){
            if( is_array($value) && isset($value[2]) ){
                $dataTable->addNumberColumn($key, $this->getNumberFormat());
            } else {
                $dataTable->addNumberColumn($key);
            }
        }

        for($date = $from->copy(); $date->lte($to); $date->addDay()):
            $day = $date->format('d/m/Y');

            $data = [$day];

            foreach($objects as $key=>$value){
                if(is_array($value)){
                    list($arrayValue, $arrayKey) = $value;
                    $data[] = get_value($arrayValue->get($day), $arrayKey, 0);
                } else {
                    $data[] = get_value($value->get($day), 'quantity', 0);
                }
            }

            $dataTable->addRow($data);
        endfor;

        return $dataTable;
    }

    /**
     * @return array
     */
    private function getDefaults()
    {
        return [
            'legend'    =>  'top',
            'chartArea' => [
                'width' =>  '90%'
            ]
        ];
    }

    private function getNumberFormat()
    {
        return new NumberFormat([
            'prefix'            => 'R$ ',
            'decimalSymbol'     => ',',
            'fractionDigits'    => 2
        ]);
    }
}