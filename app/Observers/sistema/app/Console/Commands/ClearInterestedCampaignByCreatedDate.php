<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Entities\ExternalServiceSettings;
use App\Entities\Customer;
use App\Services\SendToEvolux;
use GuzzleHttp\Client                    as HttpClient;
use GuzzleHttp\Exception\ClientException as HttpClientException;
use Monolog\Logger;
use Carbon\Carbon;

class ClearInterestedCampaignByCreatedDate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'evolux:interested-cleaning';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remover usuarios da campanha de interessados pela data de cadastro. '.
    'Removendo quando o usuario fica uma semana sem atendimento pela campanha';

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
        $Customers = Customer::whereDoesntHave('orders')->where([
            ['document_number'], //IS NULL
            [\DB::raw('DATE(created_at)'),
            '=',
            \DB::raw('DATE(date_sub("'. Carbon::now() .'", INTERVAL 1 WEEK))')],
        ]);

        $total_customers = $Customers->count();
        $customers = $Customers->get()->all();
        $msg = "Sem clientes para remover da fila de interessados";
        if ($total_customers > 0) {
            $evolux_conf = ExternalServiceSettings::where([
                ['service','='   ,'Evolux'  ],
                ['name'   ,'LIKE','%interessados%'],
            ])->first();
            foreach ($customers as $customer) {
                $evolux = new SendToEvolux($customer, $evolux_conf);
                $evolux->removeFromCampaign();
                usleep(10000);
            }
            $msg = $total_customers. " interessados removidos da fila em ".
                   Carbon::now()->format('d/m/Y H:i');
        }
        \Log::getMonolog()->log(Logger::INFO, $msg);
    }
}
