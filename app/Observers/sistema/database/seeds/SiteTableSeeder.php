<?php

use App\Entities\Company;
use App\Entities\PaymentSetting;
use App\Entities\Site;
use Illuminate\Database\Seeder;

class SiteTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $payment = PaymentSetting::create([
            "name"                  => "LOCALHOST",
            "creditcard_gateway"    => "mundipagg",
            "billet_gateway"        => "Asaas"
        ]);

        $company = Company::create([
            "name"  => "Company Teste",
            "phone" => "(99) 9999-9999",
            "cnpj"  => "00.000.000/0000-00",
            "email" => "test@example.com"
        ]);

        Site::create([
            "name"                  => "My System",
            "domain"                => "localhost",
            "color"                 => "black",
            "theme"                 => "theme-default-blue",
            "payment_setting_id"    => $payment->id,
            "company_id"            => $company->id,
            "view_folder"           => "sites/localhost"
        ]);

        Site::create([
            "name"                  => "Front End Test",
            "domain"                => "127.0.0.1",
            "color"                 => "red",
            "theme"                 => "theme-default-red",
            "payment_setting_id"    => $payment->id,
            "company_id"            => $company->id,
            "view_folder"           => "sites/localhost"
        ]);
    }
}
