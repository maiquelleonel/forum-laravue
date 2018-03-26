<?php

namespace App\Listeners;

use App\Entities\Order;
use App\Events\OrderPendingIntegration;
use App\Services\ExternalService\Contracts\ExternalService;
use App\Services\ExternalService\Slack;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;

class IntegrationErrorsSlackNotifications
{
    /**
     * Handle the event.
     *
     * @param  OrderPendingIntegration  $event
     * @return void
     */
    public function handle(OrderPendingIntegration $event)
    {
        $order = $event->order;

        $configs = $order->customer->site->slackNotifications;

        if ($configs->count()) {
            foreach ($configs as $config) {
                $errors = $this->parseAnalyzeErrors($order, $order->lastAnalyze());
                if ($errors) {
                    (new Slack($config))->sendData($errors);
                }
            }
        }
    }

    private function parseAnalyzeErrors(Order $order, Collection $analyzes)
    {
        $customer = $order->customer;
        $message = new \stdClass;
        $message->text        = "PEDIDO PENDENTE DE REVISÃO #{$order->id}\n";
        $message->text       .= $customer->firstname . " " . $customer->lastname . " | " . $customer->email;
        $message->attachments = [];

        foreach ($analyzes as $analyze) {
            if (!$analyze->status) {
                $attach = new \stdClass;
                $attach->color = "#ce0a0a";
                $attach->fallback = $analyze->rule_response;
                $attach->fields[] = (object)[
                    "title" => $analyze->rule_name,
                    "value" => $analyze->rule_response,
                    "short" => false
                ];
                $message->attachments[] = $attach;
            }
        }

        $message->attachments[] = (object)[
            "title"     => "ABRIR PEDIDO NO SISTEMA",
            "title_link"=> route("admin:orders.show", $order)
        ];

        return $message;
    }
}
