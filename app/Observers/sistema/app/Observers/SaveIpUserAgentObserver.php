<?php

namespace App\Observers;

use App\Domain\OrderStatus;
use App\Entities\Customer;
use App\Events\OrderAuthorized;
use App\Events\OrderPaid;
use App\Events\OrderRefund;
use App\Support\MobileDetect;
use Carbon\Carbon;
use App\Entities\Order;
use Illuminate\Database\Eloquent\Model;

class SaveIpUserAgentObserver
{
    /**
     * @var MobileDetect
     */
    private $mobileDetect;

    /**
     * SaveIpUserAgentObserver constructor.
     * @param MobileDetect $mobileDetect
     */
    public function __construct(MobileDetect $mobileDetect)
    {
        $this->mobileDetect = $mobileDetect;
    }

    public function creating(Model $model)
    {
        $model->ip           = $this->getClientIp();
        $model->user_agent   = request()->header('User-Agent');
        $model->device       = $this->getDevice();
    }

    public function getClientIp()
    {
        return request()->server("HTTP_CF_CONNECTING_IP")
                ?: request()->server("REMOTE_ADDR");
    }

    private function getDevice()
    {
        if ($this->mobileDetect->isMobile()) {
            return "MOBILE";
        } elseif ($this->mobileDetect->isTablet()) {
            return "TABLET";
        }
        return "DESKTOP";
    }
}
