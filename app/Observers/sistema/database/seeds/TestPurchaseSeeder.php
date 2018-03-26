<?php

use Illuminate\Database\Seeder;
use App\Entities\Customer;
use App\Entities\Order;
use App\Services\Order\CreateOrderService;

class TestPurchaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $orderService = app(CreateOrderService::class);
        $customer = Customer::create([
            'firstname'                 => 'Johnny',
            'lastname'                  => 'Test',
            'postcode'                  => '67130500',
            'address_street'            => 'Travessa WE-22',
            'address_street_number'     => '123',
            'address_street_complement' => 'casa',
            'address_street_district'   => 'Cidade Nova',
            'address_city'              => 'Ananindeua',
            'address_state'             => 'PA',
            'email'                     => 'johnny.test@gmail.com',
            'telephone'                 => '(51) 99315-7463',
            'site_id'                   => 2 ,
        ]);

        $orderService->createFromProducts(
            $customer,
            [
                (object)[
                    "name"  => "Lorem ipsum dolor et lamen",
                    "qty"   => 2,
                    "sku"   => "PRTDMXSKU2",
                    "value" => "199.90"
                ],
                [
                    "name"  => "Lorem ipsum dolor et lamen",
                    "qty"   => 1,
                    "sku"   => "PRTDMXSKU2",
                    "value" => "199.90"
                ],
            ],
            179.90,
            39.90,
            29.90
        );
    }
}
