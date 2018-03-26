<?php

use App\Entities\Product;
use Illuminate\Database\Seeder;

class ProductTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Product::create([
            'price'         => 139.90,
            'name'          => 'Product Test',
            'description'   => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
            'image'         => 'upload/default.jpg',
            'inventory'     => 500,
            'cost'          => 39.90,
            'ipi'           => 10
        ]);
    }
}
