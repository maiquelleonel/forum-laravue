<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Entities\Order;
use App\Entities\ExternalServiceSettings;
use App\Services\ExternalService\Slack;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendUpsellNotificationOnSlack extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $order;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $config  = ExternalServiceSettings::where([
            ['service', '=', 'Slack'],
            ['name'   , 'like', 'UpsellBot']
        ])->first();

        if ($config) {
            $msg = $this->formatMassage();
            (new Slack($config))->sendData($msg);
        }
    }

    private function formatMassage()
    {
        $message                = new \stdClass;
        $message->text          = "Novo Upsell #{$this->order->id}\n";
        $message->text         .= $this->order->customer->email;
        $message->attachments   = [];
        $message->attachments[] = (object)[
            "title"     => "ABRIR PEDIDO NO SISTEMA",
            "title_link"=> route("admin:orders.show", $this->order)
        ];
        return $message;
    }
}
