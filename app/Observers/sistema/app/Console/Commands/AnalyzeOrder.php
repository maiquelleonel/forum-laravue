<?php

namespace App\Console\Commands;

use App\Entities\Order;
use App\Entities\OrderAnalyzeResponse;
use App\Services\OrderAnalyzer\Analyzer;
use Illuminate\Console\Command;

class AnalyzeOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:analyze {order_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check a given order';
    /**
     * @var Analyzer
     */
    private $analyzer;

    /**
     * AnalyzeOrder constructor.
     * @param Analyzer $analyzer
     */
    public function __construct(Analyzer $analyzer)
    {
        parent::__construct();
        $this->analyzer = $analyzer;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $order = Order::find($this->argument('order_id'));
        if ($order) {
            $response = $this->analyzer->run($order);
            /** @var $ruleResponse OrderAnalyzeResponse */
            foreach ($response as $ruleResponse) {
                $text = ($ruleResponse->status ? "[V] " : "[X] ") . $ruleResponse->rule_name . ": " .
                $ruleResponse->rule_response;
                if ($ruleResponse->status) {
                    $this->info($text);
                } else {
                    $this->warn($text);
                }
            }

            $this->line("");
            $this->line("Analyze number: " . ($response->last() ? $response->last()->batch : "-"));
            $this->line("");

            $text = $response->count() . " tests, " . $response->where("status", true)->count() . " passing";

            if ($response->where("status", false)->count() >= 1) {
                $this->error("FAIL " . $text);
            } else {
                $this->info("OK ". $text);
            }
        } else {
            $this->warn("Order not found");
        }
    }
}
