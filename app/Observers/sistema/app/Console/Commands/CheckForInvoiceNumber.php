<?php

namespace App\Console\Commands;

use App\Domain\OrderStatus;
use App\Entities\Order;
use App\Jobs\CaptureInvoiceNumber;
use App\Repositories\Orders\Criteria\StatusCriteria;
use App\Repositories\Orders\Criteria\WithoutInvoiceCriteria;
use App\Repositories\Orders\OrderRepository;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class CheckForInvoiceNumber extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:check-invoices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check/Get invoice number';
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
                        ->pushCriteria( new StatusCriteria([OrderStatus::INTEGRATED]) )
                        ->pushCriteria( new WithoutInvoiceCriteria() )
                        ->all();

        $counter = 0;

        foreach ($orders as $order) {
            if ($order->customer->site->erpSetting) {
                $this->dispatch( new CaptureInvoiceNumber($order, $order->customer->site->erpSetting) );
                $counter++;
            }
        }

        $this->info("{$counter} Pedidos Aguardando Nota Fiscal");
    }
}
