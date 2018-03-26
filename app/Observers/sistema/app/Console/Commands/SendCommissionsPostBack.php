<?php

namespace App\Console\Commands;

use App\Entities\SalesCommission;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\Console\Exception\InvalidOptionException;

class SendCommissionsPostBack extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'commissions:send-postback {--start=} {--end=} {--user_id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for approved commission and resend postbacks';

    /**
     * Create a new command instance.
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
        $commissions = $this->getCommissions();

        $this->comment("Total de comissões a reenviar: {$commissions->count()}");

        if(!$this->confirm("Deseja prosseguir?")){
            return;
        }

        foreach ($commissions as $commission) {
            $this->comment("Sending PostBack: #" . $commission->order->id);
            event('eloquent.created: App\Entities\SalesCommission', $commission);
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
    protected function getCommissions()
    {
        $start      = $this->getStartDate();
        $end        = $this->getEndDate();
        $user_id    = $this->option("user_id");

        return SalesCommission::query()
                ->whereBetween("created_at", [$start, $end])
                ->where("status", SalesCommission::STATUS_APPROVED)
                ->where(function($query) use ($user_id){
                    if($user_id){
                        $query->where("user_id", $user_id);
                    }
                })
                ->get();
    }
}
