<?php

namespace App\Console\Commands;

use App\Domain\OrderStatus;
use App\Entities\Order;
use App\Entities\ExternalServiceSettings;
use App\Services\SendToEvolux;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Monolog\Logger;

class SendIntegratedCustomersToEvoluxByBundle extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'evolux:integrated-by-bundle {--interval-days=} {--remove}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enviar clientes integrados para o Evolux com base na quantidade de frascos adquiridos';

    /**
     * key = interval days
     * val = qty itens by bundle
     */
     //campanhas com bundles de

     //2 até 3 frsc ( >= 60  +dias )
     //4 até 6 frsc ( >= 120 +dias )
     //6 até 9 frsc ( >= 180 +dias )
     //rodar diariamente
    private $product_quantities = [
        60  => [2, 3],
        120 => [4, 6],
        180 => [6, 9],
    ];

    private $interval_days;
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
        if ($this->option('interval-days')) {
            $this->interval_days = $this->option('interval-days');
            $possible_intervals  = array_keys($this->product_quantities);
            if (!in_array($this->interval_days, $possible_intervals)) {
                $this->error("O intervalo deve ser um da lista => [". join(', ', $possible_intervals)."]");
                die;
            }
        }

        $qOrders      = $this->getQueryOrders();
        $total_orders = $qOrders->count();
        $orders       = $qOrders->get();
        //dd($qOrders->toSql());

        $evolux_conf = ExternalServiceSettings::where([
            ['service','='   ,'Evolux'  ],
            ['name'   ,'LIKE','%integrados '. $this->interval_days .'%'],
        ])->first();

        if ($total_orders > 0 && $evolux_conf) {
            $method = 'fire';
            if ($this->option('remove')) {
                $method = 'removeFromCampaign';
            }
            foreach ($orders as $order) {
                $evolux = new SendToEvolux($order->customer, $evolux_conf);
                $evolux->$method();
                usleep(10000);
            }
        }

        $msg = "[EVOLUX] {$total_orders} Clientes para a campanha 'integrados ". $this->interval_days. "'";
        $this->info($msg);
        \Log::getMonolog()->log(Logger::INFO, $msg);
    }

    private function getQueryOrders()
    {
        $interval_days = $this->interval_days;
        if ($this->option('remove')) {
            $interval_days += 7;
        }
        return Order::whereHas("itemsBundle.bundle.products", function ($item) {
            $item->whereIn("product_qty", $this->product_quantities[$this->interval_days]);
        })->where([[
            \DB::raw('DATE(created_at)'),
            '=',
            \DB::raw("DATE(DATE_SUB('". Carbon::now() ."', INTERVAL ". $interval_days ." DAY))")
        ]])->where(function ($q) {
            $q->orWhere('origin', '<>', 'system');
            $q->orWhere('origin');
        })->whereIn('status', [
            OrderStatus::APPROVED  ,
            OrderStatus::INTEGRATED
        ]);
    }
}
