<?php

namespace App\Console\Commands;

use App\Domain\OrderStatus;
use App\Entities\Order;
use App\Services\Commissions\AssignCommission;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\Console\Exception\InvalidOptionException;

class AssignCommissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'commissions:assign {--start=} {--end=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for approved orders without commission';

    /**
     * @var AssignCommission
     */
    protected $assignCommission;

    /**
     * Create a new command instance.
     * @param AssignCommission $assignCommission
     */
    public function __construct(AssignCommission $assignCommission)
    {
        $this->assignCommission = $assignCommission;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $orders = $this->getOrders();

        $this->comment("Total de pedidos a verificar: {$orders->count()}");

        $this->confirm("Deseja prosseguir?");

        foreach ($orders as $order) {
            $this->comment("\nPedido #{$order->id}");
            if($commission = $this->assignCommission->assign($order)){
                $this->info("Comissão atribuída: {$commission->status}");
            } else {
                $this->error("Não atribuiu commissão");
            }
        }
    }

    /**
     * @return Carbon
     */
    protected function getStartDate()
    {
        if(($start = $this->option("start")) && str_is("*-*-*", $this->option("start"))){
            return Carbon::createFromFormat("Y-m-d", $start)->startOfDay();
        }

        throw new InvalidOptionException("--start option does have format: Y-m-d");
    }

    /**
     * @return Carbon
     */
    protected function getEndDate()
    {
        if(($end = $this->option("end")) && str_is("*-*-*", $this->option("end"))){
            return Carbon::createFromFormat("Y-m-d", $end)->endOfDay();
        }

        throw new InvalidOptionException("--end option does have format: Y-m-d");
    }

    /**
     * @return Collection
     */
    protected function getOrders()
    {
        $start  = $this->getStartDate();
        $end    = $this->getEndDate();

        return Order::query()
                ->doesntHave("commissions")
                ->whereBetween("created_at", [$start, $end])
                ->whereIn("status", OrderStatus::approved())
                ->get();
    }
}
