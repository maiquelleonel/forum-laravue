<?php

namespace App\Console\Commands;

use App\Domain\OrderStatus;
use App\Jobs\SendOrderToErp;
use App\Repositories\Criterias\BeforeLastUpdateTimeCriteria;
use App\Repositories\Orders\Criteria\StatusCriteria;
use App\Repositories\Orders\OrderRepository;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class CheckForOrdersNotIntegrated extends Command
{
    use DispatchesJobs;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:integrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Integrar Pedidos';

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * Create a new command instance.
     *
     * @param OrderRepository $orderRepository
     */
    public function __construct(OrderRepository $orderRepository)
    {
        parent::__construct();
        $this->orderRepository = $orderRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $orders = $this->orderRepository
                       ->pushCriteria(new StatusCriteria([OrderStatus::APPROVED]))
                       ->pushCriteria(new BeforeLastUpdateTimeCriteria(Carbon::now()->subHours(24), "paid_at"))
                       ->findWhere(["upsell_order_id" => null]);

        $counter = 0;

        foreach ($orders as $order) {
            if ($order->customer->site->erpSetting) {
                $counter++;
                $this->dispatch(new SendOrderToErp($order, $order->customer->site->erpSetting));
            }
        }

        $this->info("{$counter} Jobs Disparados");
    }
}
