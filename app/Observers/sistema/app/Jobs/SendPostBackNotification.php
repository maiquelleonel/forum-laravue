<?php

namespace App\Jobs;

use App\Entities\PageVisit;
use App\Entities\PostBack;
use App\Services\Tracking\OutputVariableParser;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Services\PostBack\PostBack as PostBackService;

class SendPostBackNotification extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * @var PostBackService
     */
    protected $postBackService;

    /**
     * @var PostBack
     */
    private $postBack;

    /**
     * @var PageVisit
     */
    private $visit;
    /**
     * @var OutputVariableParser
     */
    private $variableParser;

    /**
     * Create a new job instance.
     *
     * @param PostBackService $postBackService
     * @param PostBack $postBack
     * @param PageVisit $visit
     * @param OutputVariableParser $variableParser
     */
    public function __construct(PostBackService $postBackService, PostBack $postBack, PageVisit $visit, OutputVariableParser $variableParser)
    {
        $this->postBackService = $postBackService;
        $this->postBack = $postBack;
        $this->visit = $visit;
        $this->variableParser = $variableParser;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $vars = $this->variableParser->getVars($this->visit);
        $url  = $this->variableParser->parseString($this->postBack->url, $vars);

        try {
            $response = $this->postBackService->sendPostBack(
                $this->postBack, $this->visit
            );
            \Log::debug("PostBack: user {$this->postBack->user_id} | {$url} | response " . $response->getStatusCode());
        } catch (\Exception $e) {
            \Log::debug("PostBack Error: user {$this->postBack->user_id} | {$url} | response " . $e->getMessage());
            if ($this->attempts() <= 3) {
                $this->release( 30 * 60 );
            }
        }
    }
}
