<?php

namespace App\Observers;

use App\Entities\Order;
use App\Entites\Customer;

class ClickIdObserver
{
    public function saving($model){
        foreach(['click_id', 'source'] as $url_param ){
            if(session()->has( $url_param )) {
                if($model->$url_param != session($url_param))
                    $model->$url_param = session($url_param);
            }
        }
    }
}
