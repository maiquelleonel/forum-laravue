<?php

namespace App\Listeners;

use App\Entities\SalesCommission;
use App\Jobs\SendPostBackNotification as SendPostBackNotificationJob;
use App\Services\PostBack\PostBack as PostBackService;
use App\Services\Tracking\OutputVariableParser;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendPostBackNotification implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels, DispatchesJobs;

    /**
     * @var PostBackService
     */
    private $postBackService;
    /**
     * @var OutputVariableParser
     */
    private $variableParser;

    /**
     * SendPostBackNotification constructor.
     * @param PostBackService $postBackService
     * @param OutputVariableParser $variableParser
     */
    public function __construct(PostBackService $postBackService, OutputVariableParser $variableParser)
    {
        $this->postBackService = $postBackService;
        $this->variableParser = $variableParser;
    }

    /**
     * Handle the event.
     *
     * @param SalesCommission $salesCommission
     */
    public function handle(SalesCommission $salesCommission)
    {
        if ($salesCommission->status == SalesCommission::STATUS_APPROVED) {
            $postBacks = $salesCommission->postBacks;
            $order     = $salesCommission->order;

            if ($order && $order->site && $order->visit) {
                foreach ($postBacks as $postBack) {
                    if ($postBack->site_id == $order->site->id) {
                        $job = new SendPostBackNotificationJob(
                            $this->postBackService,
                            $postBack,
                            $order->visit,
                            $this->variableParser
                        );
                        $this->dispatch($job);
                    }
                }
            }
        }
    }
}
