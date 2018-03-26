<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Entities\Customer;
use App\Services\SendToEvolux;
use App\Entities\Site;
use App\Entities\ExternalServiceSettings;
use Monolog\Logger;
use Carbon\Carbon;

class SendInterestedCustomersOfTheWeekToEvolux extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'evolux:interested-of-the-week';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enviar clientes interessados sem atendimento para filas do evolux';

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
        $sites = Site::where([
            ['name','LIKE','%testo%']
        ])->get()->pluck('id')->all();
        $Customers = Customer::whereDoesntHave('orders')->where([
            ['document_number'], //IS NULL
            [\DB::raw('LENGTH( TRIM( telephone ) )'), '>=', '14'],
            ['created_at', '>=',
            \DB::raw('date_sub(date_sub("'. Carbon::now() .'", INTERVAL 4 DAY), INTERVAL 21 HOUR)')],
        ])->whereIn('site_id', $sites);

        $total_customers = $Customers->count();
        $customers = $Customers->get()->all();
        //evolux conf
        $evolux_conf = ExternalServiceSettings::where([
            ['service','='   ,'Evolux'  ],
            ['name'   ,'LIKE','%semana%'],
        ])->first();
        //SendToEvolux
        $msg = "Sem clientes interassados sem atendimento";
        if ($total_customers > 0) {
            foreach ($customers as $customer) {
                $evolux = new SendToEvolux($customer, $evolux_conf);
                $evolux->fire();
                usleep(10000);
            }
            $msg = $total_customers." interessados na semana integrados com sucesso";
        }

        \Log::getMonolog()->log(Logger::INFO, "[EVOLUX] ". $msg);
    }
}
