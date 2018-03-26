<?php

namespace App\Console\Commands;

use App\Jobs\AutoUpdateCurrencyRates;
use Illuminate\Console\Command;

class UpdateCurrencyRates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'currency:update {--now}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update currency conversion rates';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if( $this->option("now") ){
            (new AutoUpdateCurrencyRates)->handle();
            $this->info("Currencies Updated");
            return;
        }

        dispatch(new AutoUpdateCurrencyRates);
        $this->info("Job Dispatched");
    }
}
