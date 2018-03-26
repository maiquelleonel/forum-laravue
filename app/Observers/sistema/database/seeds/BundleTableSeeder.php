<?php

use App\Domain\BundleCategory;
use App\Entities\Bundle;
use App\Entities\BundleGroup;
use App\Entities\Product;
use App\Entities\Site;
use Illuminate\Database\Seeder;

class BundleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $product    = Product::first();

        $group      = BundleGroup::create([
            "name"          => "BUNDLES TEST",
            "description"   => "DESCRIPTION TEST"
        ]);

        foreach(Site::all() as $site) {

            $site->bundle_group_id = $group->id;
            $site->save();

            $packs = [
                // NAME             QTY  UNITPRICE  TYPE
                ["LEVE APENAS 1", 1, 99.90, BundleCategory::PADRAO],
                ["LEVE 3 PAGUE 2", 3, 89.90, BundleCategory::PADRAO],
                ["LEVE 5 PAGUE 3", 5, 79.90, BundleCategory::PADRAO],

                // NAME             QTY  UNITPRICE  TYPE
                ["LEVE APENAS 1", 1, 79.90, BundleCategory::PROMOCIONAL],
                ["LEVE 3 PAGUE 2", 3, 69.90, BundleCategory::PROMOCIONAL],
                ["LEVE 5 PAGUE 3", 5, 59.90, BundleCategory::PROMOCIONAL],

                // UPSELL
                ["LEVE 2 TURBINADO", 2, 59.90, BundleCategory::UPSELL],
            ];

            foreach ($packs as $pack) {
                list($name, $qty, $price, $type) = $pack;
                $bundle = Bundle::create([
                    'bundle_group_id' => $group->id,
                    'image' => 'upload/default.jpg',
                    'name' => $name,
                    'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry',
                    'freight_value' => '39.90',
                    'category' => $type,
                    'installments' => '6'
                ]);

                $bundle->products()->attach([
                    [
                        'product_id' => $product->id,
                        'product_qty' => $qty,
                        'product_price' => $price
                    ]
                ]);
            }
        }
    }
}
