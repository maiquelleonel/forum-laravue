<?php

use Illuminate\Database\Seeder;
use App\Entities\ExternalServiceSettings as ESS;

class ExternalServiceSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $evolux_queues = [
            'cancelados',
            'interessados',
            'Clientes sem upsell',
            'Interessados Testomaster System Antigo',
            'remover interessado',
            'interessados da semana',
            'Clientes que geraram boletos',
            'Integrados 60+ dias',
            'Integrados 120+ dias',
            'Integrados 180+ dias',
            'Clientes com boleto vencido',
        ];
        $db_queues = [];
        foreach ($evolux_queues as $queue) {
            $db_queues[] = [
                'name'      => $queue,
                'service'   => 'Evolux',
                'auth_type' => 'BasicAuth',
                'api_key'   => 'c663cb14-30bf-4629-be1e-1c40b2f8b128',
                'base_url'  => 'https://evolux.contactamax.com/api/v1/campaign/2/subscriber',
            ];
        }
        ESS::insert($db_queues);
    }
}
