<?php

namespace App\Jobs;

use App\Entities\Currency;
use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class AutoUpdateCurrencyRates extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $apiResponse = json_decode(file_get_contents("https://api.fixer.io/latest?base=BRL"));
            if ($apiResponse->rates) {
                $currencies = Currency::where("code", "!=", "BRL")->get();

                foreach($currencies as $currency){
                    if($conversionRate = $apiResponse->rates->{$currency->code}){
                        $currency->update([
                            "conversion_rate" => $conversionRate
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            dd( $e );
        }
    }
}
