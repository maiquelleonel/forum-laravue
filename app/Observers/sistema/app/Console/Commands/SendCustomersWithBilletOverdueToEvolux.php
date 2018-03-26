<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SendToEvolux;
use App\Entities\ExternalServiceSettings;
use App\Entities\Order;
use Monolog\Logger;

class SendCustomersWithBilletOverdueToEvolux extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'evolux:billet-overdue {--remove}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enviar clientes boletos vencidos para o evolux';

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
        $evolux_conf = ExternalServiceSettings::where([
            ['service', '=', 'Evolux'],
            ['name'   , 'like', '%boleto vencido%']
        ])->first();
        $interval = 4;
        $method   = 'fire';
        $verb     = 'enviar';
        if ($this->option('remove')) {
            $interval = 11;
            $method   = 'removeFromCampaign';
            $verb     = 'remover';
        }
        if ($evolux_conf) {
            $orders = Order::with('customer')->where(function ($q) {
                $q->orWhere('origin', '<>', 'system');
                $q->orWhere('origin'); // IS NULL
            })->where([
                [\DB::raw('DATE(created_at)'), '=',
                    \DB::raw('DATE(DATE_SUB(now(), INTERVAL '.$interval.' DAY))')],
                [\DB::raw('LOWER(payment_type_collection)'),'=','boleto'],
                [\DB::raw('LOWER(status)'),'=','pendente'],
            ])->get();
            $msg = "Nenhum cliente com boleto pendente para {$verb}.";
            $total_billets = $orders->count();
            if ($total_billets > 0) {
                foreach ($orders as $order) {
                    (new SendToEvolux($order->customer, $evolux_conf))->$method();
                    usleep(10000);
                }
                $msg = $total_billets." clientes com boletos pendentes enviados";
            }
            \Log::getMonolog()->log(Logger::INFO, "[EVOLUX] ". $msg);
        }
    }
}
