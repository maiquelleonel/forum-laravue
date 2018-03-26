<?php

use Artesaos\Defender\Permission;
use Illuminate\Database\Seeder;

class ExternalSettingsPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Permission::insert([
            ["name"         => "admin:external-service-settings.index",
            "readable_name" => "Ver Configurações Externas"],
            ["name"         => "admin:external-service-settings.create" ,
            "readable_name" => "Ver formulário de cadastro de Configuração Externa"],
            ["name"         => "admin:external-service-settings.edit",
            "readable_name" => "Ver formulário de edição de Configuração Externa"],
            ["name"         => "admin:external-service-settings.update",
            "readable_name" => "Atualizar dados de Configurações Externas"],
            ["name"         => "admin:external-service-settings.store",
            "readable_name" => "Cadastrar novas Configuraçãoes Externas"],
        ]);
    }
}
