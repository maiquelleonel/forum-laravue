<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Entities\ExternalServiceSettings;
use GuzzleHttp\Client                    as HttpClient;
use GuzzleHttp\Exception\ClientException as HttpClientException;
use Monolog\Logger;

class EvoluxCampaignCleaning extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'evolux:campaign-cleaning {--ExternalServiceSettings_id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpar as filas no Evolux';

    /**
     * Create a new command instance.
     *
     * @return void
     */

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $campaign_id = $this->option('ExternalServiceSettings_id');
        $campaigns_to_clean = ExternalServiceSettings::where([
            ['id','=', $campaign_id]
        ])->get()->all(); ///testar sintaxe

        foreach ($campaigns_to_clean as $campaign) {
            $cli = new HttpClient();
            try {
                $res = $cli->request('POST', trim($campaign->base_url) .'s/clear', [
                    'form_params' => [ 'token' => $campaign->api_key ] ,
                ]);

                $msg = 'Campanha '. $campaign->name . PHP_EOL;
                \Log::getMonolog()->log(Logger::INFO, 'Evolux', [$msg . $res->getBody()]);
            } catch (HttpClientException $e) {
                \Log::getMonolog()->log(Logger::WARNING, 'Erro Evolux', [ $e->getMessage()]);
            }
        }
    }
}
