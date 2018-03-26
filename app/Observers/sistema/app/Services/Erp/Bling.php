<?php

namespace App\Services\Erp;

use App\Entities\Customer;
use App\Entities\ErpSetting;
use App\Entities\Order;
use App\Entities\Product;
use App\Services\Erp\Contracts\ErpServiceContract;
use App\Services\OrderAnalyzer\Analyzer;
use Bling\BlingSDK;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class Bling implements ErpServiceContract
{
    /**
     * @var BlingSDK
     */
    private $api;

    /**
     * @var ErpSetting
     */
    private $setting;

    /**
     * @var
     */
    private $apps = [];

    /**
     * @var array
     */
    private $ipiValues = [];

    /**
     * BlingService constructor.
     * @param ErpSetting $setting
     */
    public function __construct(ErpSetting $setting)
    {
        $this->setting = $setting;
        $this->api = new BlingSDK;
        $this->api->strApiKey = $setting->api_key;
    }

    /**
     * Retrieve NFE number by invoice number
     * @param $invoiceNumber
     * @return mixed
     */
    public function getNfeNumberByInvoice($invoiceNumber)
    {
        $response = json_decode( $this->api->getOrder($invoiceNumber, 'json') );

        return isset($response->retorno->pedidos[0]->pedido->nota->numero)
                    ?$response->retorno->pedidos[0]->pedido->nota->numero
                    : null;
    }

    /**
     * @param Order $order
     * @param bool $generateNfe
     * @return Response
     */
    public function sendOrder(Order $order, $generateNfe = false)
    {
        $this->ipiValues    = [];
        $this->apps         = [];

        $tomorrow = Carbon::tomorrow();
        if ($tomorrow->isWeekend()) {
            $tomorrow = $tomorrow->nextWeekday();
        }

        $data = [
            "cliente"       => $this->makeCustomerData( $order->customer ),
            "itens"         => $this->makeItemsData( $order ),
            "transporte"    => $this->makeShippingData( $order ),
            "pedido"        => $this->makeOrderDetails( $order ),
            "parcelas"      => $this->makeInstallmentsData( $order ),
            "vlr_frete"     => $this->getFreightValue( $order ),
            "vlr_desconto"  => $this->getIpiPercentage() && $this->apps ? 0 : $this->getOrderDiscount($order),
            "loja"          => $this->detectStoreId($order),
            "data_saida"    => $tomorrow->format("d/m/Y")
        ];

        $erpResponse = new Response();

        // @todo Refatorar para juntar os 2 pedidos e os apps
        $generateNfe = $generateNfe ? $order->approvedUpsellOrders->count() === 0 : $generateNfe;

        $response = $this->parseResponse( $this->api->postOrder( $data, $generateNfe ? "true" : false ), 'XML' );

        if (isset($response->pedidos->notafiscal->numero) && $generateNfe) {
            $erpResponse->setInvoiceNumber( $response->pedidos->notafiscal->numero );
            $nfe = $this->generateNfe($response->pedidos->notafiscal->numero, 1);
        }

        if (isset($response->pedidos->pedido->numero)) {
            $erpResponse->setInvoiceId( $response->pedidos->pedido->numero );
        }

        if (isset($response->erros->erro->cod) && $response->erros->erro->cod == "30") {
            $start  = strpos($response->erros->erro->msg, "(");
            $end    = strpos($response->erros->erro->msg, ")");
            $invoiceId = substr($response->erros->erro->msg, $start+1, $end-$start-1);
            $erpResponse->setInvoiceId( $invoiceId );

            $response = $this->parseResponse( $this->api->getOrder( $invoiceId, "json" ), "JSON" );

            if (isset($response->retorno->pedidos[0]->pedido->nota->numero)) {
                $erpResponse->setInvoiceNumber( $response->retorno->pedidos[0]->pedido->nota->numero );
            }
        }

        return $erpResponse;
    }

    /**
     * @param Customer $customer
     * @return array
     */
    private function makeCustomerData(Customer $customer)
    {
        return [
            'nome'          => $customer->firstname . ' ' . $customer->lastname,
            'tipo_pessoa'   => 'F',
            'cpf_cnpj'      => $customer->document_number,
            'endereco'      => $customer->address_street,
            'numero'        => $customer->address_street_number,
            'complemento'   => $customer->address_street_complement,
            'bairro'        => $customer->address_street_district,
            'cep'           => str_ireplace("-", "", $customer->postcode),
            'cidade'        => $customer->address_city,
            'uf'            => $customer->uf,
            'fone'          => $customer->telephone,
            'email'         => $customer->email
        ];
    }

    /**
     * @param Order $order
     * @return array
     */
    private function makeItemsData(Order $order)
    {
        $items = [];

        $items = array_merge($items, $this->makeItemsBundle($this->getItemsBundle($order)));
        $items = array_merge($items, $this->makeItemsProduct($this->getItemsProduct($order)));
        $items = array_merge($items, $this->makeItemsApps($order, $this->apps));

        return $items;
    }

    private function getItemsBundle(Order $order)
    {
        $items = $order->bundles;

        foreach ($order->approvedUpsellOrders as $upsellOrder) {
            $bundles = $upsellOrder->bundles;
            if($bundles->count()){
                $items = $items->merge($bundles);
            }
        }

        return $items;
    }

    private function getItemsProduct(Order $order)
    {
        $items = $order->products;

        foreach ($order->approvedUpsellOrders as $upsellOrder) {
            $products = $upsellOrder->products;
            if($products->count()){
                $items = $items->merge($products);
            }
        }

        return $items;
    }


    /**
     * @param $bundles
     * @return array
     */
    private function makeItemsBundle($bundles)
    {
        $items = [];

        foreach ($bundles as $bundle) {
            foreach($bundle->products as $product) {
                if ($product->is_app) {
                    $this->addApp($product, $product->pivot->product_qty, $product->pivot->product_price);
                } else {
                    $this->addIpiValue($product->ipi, $product->pivot->product_qty * $product->pivot->product_price);
                    $items[] = [
                        "item" => $this->makeItemData($product, $product->pivot->product_qty, $product->pivot->product_price)
                    ];
                }
            }
        }

        return $items;
    }

    /**
     * @param $products
     * @return array
     */
    private function makeItemsProduct($products)
    {
        $items = [];

        foreach ($products as $product) {
            if ($product->is_app) {
                $this->addApp($product, $product->pivot->qty, $product->pivot->price);
            } else {
                $this->addIpiValue($product->ipi, $product->pivot->qty * $product->pivot->price);
                $items[] = [
                    "item" => $this->makeItemData($product, $product->pivot->qty, $product->pivot->price)
                ];
            }
        }

        return $items;
    }

    /**
     * @param $product
     * @param $qty
     * @param $price
     */
    private function addApp($product, $qty, $price)
    {
        $this->apps[] = (Object) [
            'product'   => $product,
            'qty'       => $qty,
            'price'     => $price
        ];
    }

    /**
     * @param $order
     * @param $apps
     * @return array
     */
    private function makeItemsApps(Order $order, $apps)
    {
        $items = [];

        $appValue = 0;

        $total    = $this->getOrderTotal( $order );
        $discount = $this->getOrderDiscount( $order );
        $freight  = $this->getFreightValue( $order );

        if ($this->setting->discount_ipi_in_apps && count($apps) > 0) {
            if ($this->getIpiPercentage()) {
                $value = $this->calculateAppsValue($total - $discount, $freight);
            } else {
                $value = $this->calculateAppsValue($total, $freight);
            }
            $appValue = number_format($value / collect($apps)->sum("qty"), 2, '.', '');
        }

        foreach ($apps as $app) {
            $items[] = [
                "item"  => $this->makeItemData($app->product, $app->qty, $appValue ?: $app->price)
            ];
        }

        return $items;
    }

    /**
     * @param Product $product
     * @param $qty
     * @param $unitPrice
     * @return array
     */
    private function makeItemData(Product $product, $qty, $unitPrice)
    {
        return [
            'codigo'    => $product->sku,
            'descricao' => $product->label ?: $product->name,
            'qtde'      => (string) $qty,
            'vlr_unit'  => $unitPrice,
            'tipo'      => 'P',
            'un'        => 'un',
            'origem'    => '0'
        ];
    }

    /**
     * @param $ipi
     * @param $value
     */
    private function addIpiValue($ipi, $value)
    {
        if(isset($this->ipiValues[$ipi])) {
            $this->ipiValues[$ipi] += $value;
            return;
        }

        $this->ipiValues[$ipi] = $value;
    }

    /**
     * @param $totalProducts
     * @param $freightValue
     * @return mixed
     */
    private function calculateAppsValue($totalProducts, $freightValue)
    {
        $itemsPrice = array_sum($this->ipiValues);
        $ipi = $this->getIpiPercentage();

        if ($ipi > 0) {
            $ipi        = $ipi/100;
            $produtos   = $itemsPrice;
            $frete      = $freightValue;
            $nota       = $totalProducts + $freightValue;

            $b = ($produtos * $ipi + $produtos + ($frete + $produtos) - $nota);
            $c = ($frete * $ipi + $produtos*$ipi + ($frete + $produtos) - $nota) * $produtos;

            return quadratic(1, $b, $c, 'root1');
        }

        return 0;
    }

    /**
     * @return int
     */
    private function getIpiPercentage()
    {
        if (count($this->ipiValues) > 0) {
            $ipi = array_keys( $this->ipiValues );
            arsort($ipi);
            $ipi = array_values($ipi);
            return isset($ipi[0]) ? $ipi[0] : 0;
        }

        return 0;
    }

    /**
     * @param Order $order
     * @return array
     */
    private function makeShippingData(Order $order)
    {
        return [
            'dados_etiqueta' => [
                'nome'          => $order->customer->firstname . ' ' . $order->customer->lastname,
                'endereco'      => $order->customer->address_street,
                'numero'        => $order->customer->address_street_number,
                'complemento'   => $order->customer->address_street_complement,
                'bairro'        => $order->customer->address_street_district,
                'municipio'     => $order->customer->address_city,
                'uf'            => $order->customer->uf,
                'cep'           => str_ireplace([".", "-", "_"], "", $order->customer->postcode)
            ],
            'tipo_frete'    =>  'D',
            'qtde_volumes'  => '1'
        ];
    }

    /**
     * @param Order $order
     * @return array
     */
    private function makeOrderDetails(Order $order)
    {
        return [
            'numero'        => $order->id,
            'numero_loja'   => $order->id
        ];
    }

    /**
     * @param Order $order
     * @return array
     */
    private function makeInstallmentsData(Order $order)
    {
        $total      = $this->getOrderTotal($order);
        $discount   = $this->getOrderDiscount($order);
        $freight    = $this->getFreightValue($order);
        $orderInstallments = $this->getOrderInstallments($order);

        $installments = [];
        $installmentValue = ($total - $discount+ $freight) / $orderInstallments;

        foreach (range(1, $orderInstallments) as $installment) {
            $installments[] = [
                "parcela" => [
                    'vlr'   => $installmentValue,
                    'dias'  => $installment * 30
                ]
            ];
        }

        return $installments;
    }

    /**
     * @param $responseString
     * @param $responseFormat
     * @return mixed|object
     */
    private function parseResponse($responseString, $responseFormat)
    {
        switch (mb_strtoupper($responseFormat)) {
            case "XML":
                return $this->xmlToJson( $responseString );

            case "JSON":
                return json_decode( $responseString );
        }

        return $responseString;
    }

    /**
     * @param $xmlString
     * @return object
     */
    private function xmlToJson($xmlString)
    {
        $xmlString = str_replace(array("\n", "\r", "\t"), '', $xmlString);
        $xmlString = trim(str_replace('"', "'", $xmlString));
        $simpleXml = simplexml_load_string($xmlString);
        $json = json_encode($simpleXml);

        return json_decode( $json );
    }

    /**
     * @param Order $order
     * @return null|string
     */
    private function detectStoreId(Order $order)
    {
        $paymentType = mb_strtoupper( $order->payment_type_collection );

        $setting = $this->setting;

        if( $setting->credit_card_store_id && $paymentType == "CREDITCARD" ) {

            return $setting->credit_card_store_id;

        } else if ($setting->billet_store_id && $paymentType == "BOLETO") {

            return $setting->billet_store_id;

        } else if ($setting->others_store_id) {

            $setting->others_store_id;

        }

        return null;
    }

    /**
     * @param Order $order
     * @return Collection
     */
    public function validate(Order $order)
    {
        /** @var $analyzer Analyzer */
        $analyzer = app(Analyzer::class);
        return $analyzer->run($order);
    }

    private function generateNfe($number, $serie = 1)
    {
        try {
            $url = "https://bling.com.br/Api/v2/notafiscal/";

            $data = [
                "apikey"    => $this->setting->api_key,
                "number"    => $number,
                "serie"     => $serie,
                "sendEmail" => "true"
            ];

            $curl_handle = curl_init();
            curl_setopt($curl_handle, CURLOPT_POST, TRUE);
            curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $data);
            curl_setopt($curl_handle, CURLOPT_URL, $url);
            curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, TRUE);
            $response = curl_exec($curl_handle);
            curl_close($curl_handle);
            return $response;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function getFreightValue($order)
    {
        $freight = $order->freight_value ?: 0;
        $freight+= $order->approvedUpsellOrders->sum("freight_value");

        return $freight;
    }

    private function getOrderDiscount($order)
    {
        $discount = $order->discount;
        $discount+= $order->approvedUpsellOrders->sum("discount");

        return $discount;
    }

    private function getOrderTotal($order)
    {
        $total = $order->total;
        $total+= $order->approvedUpsellOrders->sum("total");

        return $total;
    }

    private function getOrderInstallments($order)
    {
        if ($order->approvedUpsellOrders->max("installments") > $order->installments) {
            return $order->approvedUpsellOrders->max("installments");
        }

        return $order->installments;
    }
}