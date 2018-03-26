<?php

use Artesaos\Defender\Permission;
use Illuminate\Database\Seeder;

class EvoluxLoginReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Permission::insert([
            [
                "name"          => "admin:report.evolux-login",
                "readable_name" => "Ver Relatório de ponto do evolux"
            ],
            [
                "name"          => "admin:report.evolux-login-process",
                "readable_name" => "Processar Relatório de ponto do evolux"
            ],
        ]);
    }
}
