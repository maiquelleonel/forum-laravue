<?php

namespace App\Console\Commands;

use App\Domain\BundleCategory;
use App\Entities\Bundle;
use App\Entities\BundleGroup;
use App\Entities\BundleProduct;
use App\Entities\Product;
use Illuminate\Console\Command;

class MakeDefaultBundles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:bundles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make Default Bundles';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $product = $this->getProduct();

        $bundleGroup = $this->getBundleGroup();

        $categories = $this->getCategories();

        $this->makeBundles($product, $bundleGroup, $categories);
    }

    /**
     * Get product
     * @return Product
     */
    private function getProduct()
    {
        $product = null;
        do {
            $id = $this->ask("Digite o ID do produto");
            $product = Product::find($id);
            if (isset($product->name)) {
                if (!$this->confirm("CONFIRMA PRODUTO [ " . $product->name . " ]", true)) {
                    $product = null;
                }
            } else {
                $this->error("Produto nao existe");
            }
        } while (!$product);

        return $product;
    }

    /**
     * @return BundleGroup
     */
    private function getBundleGroup()
    {
        $group = null;
        do {
            $id = $this->ask("Digite o ID do grupo");
            $group = BundleGroup::find($id);
            if (isset($group->name)) {
                if (!$this->confirm("CONFIRMA GRUPO [ " . $group->name . " ]", true)) {
                    $group = null;
                }
            } else {
                $this->error("Grupo nao existe");
            }
        } while (!$group);

        return $group;
    }

    /**
     * Get Bundles/Items/Categories
     * @return array
     */
    private function getCategories()
    {
        $bundle = function ($name, $installments, $items) {
            return (Object) [
                "name"          => $name,
                "installments"  => $installments,
                "items"         => $items
            ];
        };
        return [
            BundleCategory::PADRAO => [
                $bundle("2 Frascos - Tratamento Básico", 2, [
                    [2, 89.95]
                ]),
                $bundle("4 Frascos - Tratamento Popular", 4, [
                    [4, 76.00]
                ]),
                $bundle("6 Frascos - Tratamento Recomendado", 6, [
                    [6, 66.00]
                ])
            ],

            BundleCategory::UPSELL => [
                $bundle("6 Frascos - Tratamento Básico Turbinado", 4, [
                    [6, 59.97]
                ]),
                $bundle("12 Frascos - Tratamento Popular Turbinado", 8, [
                    [12, 50.67]
                ]),
                $bundle("18 Frascos - Tratamento Recomendado Turbinado", 12, [
                    [18, 44.00]
                ])
            ],

            BundleCategory::PROMOCIONAL => [
                $bundle("Compre 2 e Leve 3", 3, [
                    [3, 59.97]
                ]),
                $bundle("Compre 4 e Leve 6", 6, [
                    [6, 50.67]
                ]),
                $bundle("Compre 6 e Leve 9", 9, [
                    [9, 44.00]
                ])
            ]
        ];
    }

    private function makeBundles($product, $group, $categories)
    {
        foreach ($categories as $category => $bundles) {
            foreach ($bundles as $bundle) {
                $bundleModel = Bundle::firstOrCreate([
                    "name"              => $bundle->name,
                    "bundle_group_id"   => $group->id,
                    "category"          => $category
                ]);

                $bundleModel->products()->detach();

                foreach ($bundle->items as $item) {
                    list($qty, $price) = $item;
                    BundleProduct::create([
                        'bundle_id'     => $bundleModel->id,
                        'product_id'    => $product->id,
                        'product_qty'   => $qty,
                        'product_price' => $price
                    ]);
                }

                $bundleModel->installments = $bundle->installments;
                $bundleModel->image = $bundleModel->image ?: "upload/default.jpg";
                $bundleModel->description = $bundleModel->description ?: "Nós garantimos a eficácia de nossos produtos, e se por alguma razão você estiver insatisfeito com qualquer de suas compras, devolveremos o seu dinheiro.";
                $bundleModel->freight_value = $bundleModel->freight_value ?: 39.90;
                $bundleModel->save();
            }
        }
    }
}
