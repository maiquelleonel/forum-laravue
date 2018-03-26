<?php

namespace App\Presenters;

use App\Support\MobileDetect;
use Laracasts\Presenter\Presenter;
use App\Services\Payment\Response\CreditCard;

/**
 * Class CustomerPresenter
 *
 * @package namespace App\Presenters;
 */
class CustomerPresenter extends Presenter
{
    public function fullName()
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    public function fullNameUpper()
    {
        return mb_strtoupper($this->fullName());
    }

    public function plainDocument()
    {
        return str_ireplace(["-", " "], "", $this->document_number);
    }

    public function fullAddress()
    {
        return implode(', ', array_where([
                $this->postcode,
                $this->address_street,
                $this->address_street_number,
                $this->address_street_complement,
                $this->address_street_district,
                $this->address_city,
                $this->uf], function($key, $value){
                    return !empty($value);
        }));
    }

    public function plainPhone()
    {
        return '0' . str_ireplace([' ', '(', ')', '-'], "", $this->telephone);
    }

    public function lastUpdate()
    {
        return $this->updated_at->format('d/m/Y H\hi');
    }

    public function created()
    {
        return $this->created_at->format('d/m/Y H\hi');
    }

    public function device()
    {
        if ($agent = $this->entity->user_agent) {
            $detector = new MobileDetect(null, $this->entity->user_agent);

            if ($detector->isMobile()) {
                $device = "mobile";
            } else if ($detector->isTablet()) {
                $device = "tablet";
            } else {
                $device = "desktop";
            }

            return "<i class='fa fa-{$device}' title='Último acesso no {$device}'></i>";
        }
    }
}