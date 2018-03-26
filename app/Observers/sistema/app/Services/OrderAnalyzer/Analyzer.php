<?php

namespace App\Services\OrderAnalyzer;

use App\Entities\Order;
use App\Entities\OrderAnalyzeResponse;
use App\Services\OrderAnalyzer\Contracts\OrderAnalyzerRuleContract;
use App\Services\OrderAnalyzer\Rules\CustomerWithoutCPF;
use App\Services\OrderAnalyzer\Rules\MaxApprovedTransactionsAllowed;
use App\Services\OrderAnalyzer\Rules\OrderWithoutApp;
use App\Services\OrderAnalyzer\Rules\ValidateAddressStreet;
use App\Services\OrderAnalyzer\Rules\ValidateAddressStreetNumber;
use App\Services\OrderAnalyzer\Rules\ValidateZipcodeInCorreios;
use App\Support\SiteSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class Analyzer
{
    private $rules = [
        MaxApprovedTransactionsAllowed::class,
        ValidateAddressStreetNumber::class,
        //ValidateZipcodeFromAddress::class,
        ValidateZipcodeInCorreios::class,
        OrderWithoutApp::class,
        CustomerWithoutCPF::class
    ];

    /**
     * @param Order $order
     * @return Collection
     */
    public function run(Order $order)
    {
        $response = collect();

        $batch = $this->getNextBatch($order);

        $this->resolveDependencies( $order );

        foreach($this->rules as $ruleClass){
            /** @var $rule OrderAnalyzerRuleContract */
            $rule = app($ruleClass);

            try {
                $passes = $rule->passes($order);
                $response->push($this->response($order->id, $batch, $rule->name(), $passes ? "OK" : $rule->message(), $passes));
            } catch (\Exception $e) {
                $response->push($this->response($order->id, $batch, $rule->name(), $e->getMessage(), false));
            }
        }

        return $response;
    }

    private function getNextBatch($order)
    {
        if($batch = $order->analyzes()->max("batch") ){
            return $batch + 1;
        }
        return 1;
    }

    private function response($orderId, $batch, $ruleName, $ruleMessage, $status)
    {
        return OrderAnalyzeResponse::create([
            "order_id"      => $orderId,
            "rule_name"     => $ruleName,
            "rule_response" => $ruleMessage,
            "batch"         => $batch,
            "status"        => $status
        ]);
    }

    private function resolveDependencies($order)
    {
        app()->singleton(SiteSettings::class, function() use ($order){
            $settings = new SiteSettings(new Request());
            $settings->init($order->customer->site);
            return $settings;
        });
    }
}
