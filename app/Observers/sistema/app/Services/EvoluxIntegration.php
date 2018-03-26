<?php
/*
ex.: http://maxformula.evolux.net.br/api/v1/campaign/$id/subscriber
    passando os dados:
    name=Fulano de Tal
    &external_id=06961000000 #id no banco da maxformula
    &number=(11) 4118-6256 ##SEM _ nem () ou -
    &token=<TOKEN> #gerado no evolux
*/

namespace App\Services;

use App\Entities\Site;
use App\Entities\Customer;
use App\Entities\Order;
use App\Entities\ExternalServiceSettings;
use App\Domain\OrderStatus;
use App\Domain\BundleCategory;
use Carbon\Carbon;
use Monolog\Logger;


class EvoluxIntegration {

    private $queue_cancel   = null;

    private $queue_interest = null;

    private $queue_old_interest = null;

    private $queue_upsell   = null;

    private $interval_number= null;

    private $interval_unit  = null;

    private $site_name      = null;

    private $evolux_conf    = null;

    private $sites          = [];

    public function __construct(){
        $this->get_queues();
    }

    public function set_interval($number="", $unit=""){
        if( is_numeric($number) ){
            $this->interval_number = $number;
        }else{
            $this->interval_number = 30;
        }

        $unit = strtoupper($unit);
        if( in_array($unit, ['MINUTE','HOUR','DAY','WEEK','MONTH']) ){
            $this->interval_unit = $unit;
        }else{
            $this->interval_unit = 'MINUTE';
        }
    }

    public function get_queues(){

        $this->queues = ExternalServiceSettings::has('sites')
                        ->where('service','=','Evolux')->get();
        $vars = [
            'interessados' => 'queue_interest'    ,
            'cancelados'   => 'queue_cancel'      ,
            'upsell'       => 'queue_upsell'      ,
            'system antigo'=> 'queue_old_interest',
        ];
        foreach($this->queues as $queue){
            foreach($vars as $human => $var){
                if(stripos($queue->name, $human) !== false && is_null($this->$var)){
                    $this->$var = $queue;
                    $this->sites[$queue->name] = $queue->sites()->get()->pluck('name')->toArray();
                }
            }
        }

    }

    public function get_interval(){
        if(is_null($this->interval_number) && is_null($this->interval_unit)){
            $this->set_interval();
        }

        return \DB::raw(
            "DATE_SUB('". Carbon::now() ."', INTERVAL ". $this->interval_number ." ". $this->interval_unit .")"
        );
    }

    public function fire(){

        $sites = Site::has('externalServices')
                    ->with('externalServices')->get();
        foreach ($sites as $site){
            $this->site_name = $site->name;
            $this->customers = $site->customers()->where([
                ['created_at', '>=', $this->get_interval() ] ,
                ['created_at', '<=', \DB::raw("DATE_SUB('". Carbon::now() ."', INTERVAL 30 MINUTE)") ],
            ])->get();
            $this->send_customers_data_to_evolux();
        }
    }

    private function send_customers_data_to_evolux(){

        if(count($this->customers)){

            foreach($this->customers as $customer){

                $this->resolve_evolux_conf($customer);

                if( is_object($this->evolux_conf) && $this->is_valid_phone($customer->telephone) ){
                    $send = new SendToEvolux( $customer, $this->evolux_conf );
                    $send->fire();
                }
            }
        }else{
            $this->log(Logger::INFO, "Integraçao Evolux", "Sem clientes para integrar no periodo em: ". $this->site_name );
        }
    }

    private function resolve_evolux_conf($customer){
        $this->evolux_conf = null;
        $customer_latest_order = $customer->orders()->latest()->first();

        if( is_null($customer->document_number) && is_null($customer_latest_order) ){
            $this->evolux_conf = $this->queue_interest;
            if( $customer->site_id == 23 ){
                $this->evolux_conf = $this->queue_old_interest;
            }

        }elseif( is_object($customer_latest_order) &&
                 strtolower($customer_latest_order->origin) != 'system' ){

            $payment_type_collection = strtolower($customer_latest_order->payment_type_collection);
            $bundle = $customer_latest_order->bundles()->latest()->first();

            if($payment_type_collection == 'creditcard'){
                switch ($customer_latest_order->status) {
                    case OrderStatus::APPROVED   :
                    case OrderStatus::INTEGRATED :
                        if( is_object($bundle) && $bundle->category != BundleCategory::UPSELL ){
                            if( $this->site_on_queue($this->queue_upsell) ){
                                $this->evolux_conf = $this->queue_upsell;
                            }
                        }
                        break;
                }
            }
        }
    }

    private function is_valid_phone($telephone){
        return !in_array($telephone,['', null]);
    }

    private function log($type, $title, $message=null, $params=null){
        $info = [];
        foreach(['message', 'params'] as $var){
            if(! is_null($$var) ){
                $info[$var] = $$var;
            }
        }

        \Log::getMonolog()->log($type,$title, $info);
    }

    public function site_on_queue($queue){
        $ret = false;
        if(is_object($queue)){
            if(in_array($this->site_name, $this->sites[$queue->name])){
                $ret = true;
            }
        }
        return $ret;
    }
}
