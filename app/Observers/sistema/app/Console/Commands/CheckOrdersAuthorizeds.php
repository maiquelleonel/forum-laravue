<?php

namespace App\Console\Commands;

use App\Domain\OrderStatus;
use App\Entities\Order;
use App\Jobs\CheckOrderStatus;
use App\Repositories\Criterias\BeforeCreatedTimeCriteria;
use App\Repositories\Orders\Criteria\StatusCriteria;
use App\Repositories\Orders\OrderRepository;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class CheckOrdersAuthorizeds extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:check-authorized';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check authorized orders waiting more than 48 hours';
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
                        ->pushCriteria(new StatusCriteria([OrderStatus::AUTHORIZED]))
                        ->pushCriteria(new BeforeCreatedTimeCriteria(Carbon::now()->subHours(48)))
                        ->all();

        $this->info($orders->count() . " pedidos a serem verificados");

        /**
         * @var $order Order
         */
        foreach ($orders as $order) {
            $this->dispatch(new CheckOrderStatus($order));
        }
    }
}
