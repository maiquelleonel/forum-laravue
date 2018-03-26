<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EvoluxIntegration;
use Monolog\Logger;

class SendInterestedCustomersToEvolux extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'evolux:integrate {--latest-time=} {--unit=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enviar clientes para filas do evolux';

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
        \Log::getMonolog()->log(Logger::DEBUG, "disparando fila dos interessados");
        $evolux      = new EvoluxIntegration();
        $latest_time = false;
        $unit        = false;

        if ($this->option('latest-time')) {
            $latest_time = $this->option('latest-time');
        }

        if ($this->option('unit')) {
            $unit = $this->option('unit');
        }

        $evolux->set_interval($latest_time, $unit);
        $evolux->fire();
        \Log::getMonolog()->log(Logger::DEBUG, "finalizando fila dos interessados");
    }
}
