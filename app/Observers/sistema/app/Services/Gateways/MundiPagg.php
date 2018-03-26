<?php

namespace App\Services\Gateways;

use App\Entities\PaymentSetting;
use Dlimars\CreditCardReturns\Acquirers\BaseAcquirer;
use Gateway\One\DataContract\Common\Address;
use Gateway\One\DataContract\Enum\AddressTypeEnum;
use Gateway\One\DataContract\Enum\BuyerCategoryEnum;
use Gateway\One\DataContract\Enum\CountryEnum;
use Gateway\One\DataContract\Enum\DocumentTypeEnum;
use Gateway\One\DataContract\Enum\EmailTypeEnum;
use Gateway\One\DataContract\Enum\PersonTypeEnum;
use Gateway\One\DataContract\Request\CancelRequest;
use App\Entities\Customer;
use App\Services\Gateways\Contracts\CreditCard;
use Gateway\ApiClient;
use Gateway\One\DataContract\Enum\ApiEnvironmentEnum;
use Gateway\One\DataContract\Enum\CreditCardOperationEnum;
use Gateway\One\DataContract\Enum\PaymentMethodEnum;
use Gateway\One\DataContract\Report\ApiError;
use Gateway\One\DataContract\Report\CreditCardError;
use Gateway\One\DataContract\Request\CreateSaleRequest;
use Gateway\One\DataContract\Request\CreateSaleRequestData\ShoppingCart;
use Gateway\One\DataContract\Request\CreateSaleRequestData\ShoppingCartItem;
use Gateway\One\Helper\CreditCardHelper;
use Gateway\One\DataContract\Request\CreateSaleRequestData\CreditCard as CreditCardObject;
use Illuminate\Support\Collection;

class MundiPagg implements CreditCard
{
    private $customer;

    private $creditCard;

    private $totalInCents;

    private $saleRequest;

    private $installments;

    /**
     * @var ApiClient
     */
    private $api;

    /**
     * @var
     */
    private $paymentMethod;

    /**
     * @var string
     */
    private $description;

    /**
     * @var CancelRequest
     */
    private $cancelRequest;

    /**
     * @var \App\Entities\PaymentSetting
     */
    private $paymentSettings;

    /**
     * @var ShoppingCart
     */
    private $shoppingCart;

    /**
     * MundiPagg constructor.
     * @param PaymentSetting $paymentSettings
     * @throws \Exception
     */
    public function __construct(PaymentSetting $paymentSettings)
    {
        $this->paymentSettings = $paymentSettings;
        $this->api = new ApiClient;
        $environment = $this->getEnvironment($paymentSettings->mundipagg_environment);
        $this->api->setEnvironment($environment);
        $this->api->setMerchantKey($paymentSettings->mundipagg_merchantkey);
        $this->api->setBaseUrl($this->getBaseUrl($environment));
        $this->paymentMethod = $this->getPaymentMethod($paymentSettings->mundipagg_payment_method);
        $this->saleRequest = new CreateSaleRequest;
        $this->cancelRequest = new CancelRequest;
        $this->shoppingCart = new ShoppingCart;
    }

    /**
     * @param $environment
     * @return mixed
     */
    private function getEnvironment($environment)
    {
        if ($environment) {
            return constant(ApiEnvironmentEnum::class."::".$environment);
        }

        return ApiEnvironmentEnum::SANDBOX;
    }

    /**
     * @param $environment
     * @return string
     * @throws \Exception
     */
    private function getBaseUrl($environment)
    {
        switch ($environment) {
            case ApiEnvironmentEnum::PRODUCTION:
                return 'https://transactionv2.mundipaggone.com';

            case ApiEnvironmentEnum::SANDBOX:
                return 'https://sandbox.mundipaggone.com';

            case ApiEnvironmentEnum::INSPECTOR:
                return 'https://stagingv2-mundipaggone-com-9blwcrfjp9qk.runscope.net';

            case ApiEnvironmentEnum::TRANSACTION_REPORT:
                return 'https://api.mundipaggone.com';

            default:
                throw new \Exception("The api environment was not defined.");
        }

    }


    /**
     * @param $method
     * @return mixed
     */
    private function getPaymentMethod($method)
    {
        if (is_numeric($method)) {
            return $method;
        }
        return $method ? constant(PaymentMethodEnum::class."::".$method) : "0";
    }

    /**
     * @inheritdoc
     */
    public function setTotal($totalInCents)
    {
        $this->totalInCents = $totalInCents;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setInstallments($installments)
    {
        $this->installments = $installments;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setCustomer(Customer $customer)
    {
        $this->customer = $customer;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setOperation($operation)
    {
        $this->operation = $operation;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setCreditCard($card)
    {
        if (is_string($card)) {
            $this->creditCard = (Object) [
                'instantBuyKey' => $card,
            ];
        } else if (is_array($card)) {
            if (isset($card['instant_buy_key']) && !empty($card['instant_buy_key'])) {
                $this->creditCard = (Object) [
                    'instantBuyKey' => $card['instant_buy_key'],
                ];
            } else {
                $this->creditCard = (Object) [
                    'number'    => isset($card['number']) ? $card['number'] : null,
                    'name'      => isset($card['name']) ? $card['name'] : null,
                    'expiry'    => isset($card['month'], $card['year']) ? $card['month']."/".$card['year'] : null,
                    'cvv'       => isset($card['cvv']) ? $card['cvv'] : null
                ];
            }
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCreditCard()
    {
        if ($card = $this->creditCard) {

            if (isset($card->instantBuyKey)) {
                $creditCard = new CreditCardObject;
                $creditCard->setInstantBuyKey($card->instantBuyKey);
                return $creditCard;
            }

            return CreditCardHelper::createCreditCard(
                $card->number,
                $card->name,
                $card->expiry,
                $card->cvv
            );
        }
        return null;
    }

    /**
     * @param $operation
     * @return CreateSaleRequest
     */
    private function getSaleRequest($operation)
    {
        $this->addCreditCardTransaction($operation);

        $this->addBuyer($this->customer);

        $this->addShoppingCart($this->shoppingCart);

        return $this->saleRequest;
    }

    /**
     * @param $operation
     */
    private function addCreditCardTransaction($operation)
    {
        $this->saleRequest
                ->addCreditCardTransaction()
                ->setPaymentMethodCode($this->paymentMethod)
                ->setCreditCardOperation($operation)
                ->setInstallmentCount($this->installments)
                ->setAmountInCents($this->totalInCents)
                ->setCreditCard($this->getCreditCard());

        if ($this->description OR $this->paymentSettings->mundipagg_softdescriptor) {
            $description = mb_strtoupper( $this->description ?: $this->paymentSettings->mundipagg_softdescriptor);
            foreach($this->saleRequest->getCreditCardTransactionCollection() as &$creditCard) {
                $creditCard->getOptions()->setSoftDescriptorText($description);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function setIdentifier($identifier)
    {
        $identifier = $this->paymentSettings->mundipagg_transaction_prefix . $identifier . "_" . substr( md5( microtime() ), 0, 5 );

        $this->saleRequest
             ->getOrder()
             ->setOrderReference( $identifier );

        return $this;
    }

    /**
     * @param $customer
     */
    private function addBuyer($customer)
    {
        $buyer = $this->saleRequest->getBuyer();

        $buyer->setName($customer->firstname . ' ' . $customer->lastname)
            ->setPersonType(PersonTypeEnum::PERSON)
            ->setBuyerReference($customer->id)
            ->setBuyerCategory(BuyerCategoryEnum::NORMAL)
            ->setDocumentNumber($customer->plain_document_number)
            ->setDocumentType(DocumentTypeEnum::CPF)
            ->setEmail($customer->email)
            ->setEmailType(EmailTypeEnum::PERSONAL)
            ->setHomePhone($customer->formatted_telephone)
            ->setMobilePhone($customer->formatted_telephone)
            ->setCreateDateInMerchant($customer->created_at)
            ->setLastBuyerUpdateInMerchant($customer->updated_at);

        $address = new Address();

        $address
            ->setAddressType(AddressTypeEnum::RESIDENTIAL)
            ->setStreet($customer->address_street)
            ->setNumber($customer->address_street_number)
            ->setComplement($customer->address_street_complement)
            ->setDistrict($customer->address_street_district)
            ->setCity($customer->address_city)
            ->setState($customer->uf)
            ->setZipCode($customer->postcode)
            ->setCountry(CountryEnum::BRAZIL);

        $buyer->addAddress( $address );
    }

    /**
     * Set Description
     * @param String $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function makeAuthAndCapture()
    {
        return $this->makeTransaction(CreditCardOperationEnum::AUTH_AND_CAPTURE);
    }
    /**
     * @inheritdoc
     */
    public function makeAuth()
    {
        return $this->makeTransaction(CreditCardOperationEnum::AUTH_ONLY);
    }

    /**
     * @inheritdoc
     */
    public function makeCapture()
    {
        return $this->makeTransaction(CreditCardOperationEnum::AUTH_AND_CAPTURE);
    }

    /**
     * @inheritdoc
     */
    public function makeCancel($acquirerOrderKey, $storeKey)
    {
        $this->api->setMerchantKey($storeKey);
        $this->cancelRequest->setOrderKey($acquirerOrderKey);
        return $this->makeTransaction("CANCEL");
    }

    public function makeConsult($orderKey, $merchantKey = null)
    {
        if ($merchantKey) {
            $this->api->setMerchantKey( $merchantKey );
        };

        $response = $this->api->searchSaleByOrderKey($orderKey);

        return new PaymentResponse(
            $response->isSuccess(), $response->isSuccess() ? 201 : 401, null, $response->getData(),
            json_encode(["Orderkey"=>$orderKey]),
            json_encode($response->getData())
        );
    }

    /**
     * @param string $operation see CreditCardOperationEnum class
     * @return PaymentResponse
     */
    private function makeTransaction($operation)
    {
        return $this->callApi(function() use ($operation){
            if (in_array($operation, [CreditCardOperationEnum::AUTH_AND_CAPTURE, CreditCardOperationEnum::AUTH_ONLY])) {
                return $this->api->createSale($this->getSaleRequest($operation));
            } else if ($operation == "CANCEL") {
                return $this->api->cancel($this->cancelRequest);
            } else {
                throw new \Exception("Invalid operation type");
            }
        });
    }

    /**
     * @param $request
     * @return mixed
     */
    private function parseCreditCardNumberRequest($request)
    {
        if (isset($request['CreditCardTransactionCollection'])) {
            foreach ($request['CreditCardTransactionCollection'] as &$data) {
                if (isset($data['CreditCard']['CreditCardNumber'])) {
                    $data['CreditCard']['CreditCardNumber'] = substr_replace($data['CreditCard']['CreditCardNumber'], "xxxxx", 6, -4);
                    $data['CreditCard']['ExpMonth']         = 'xx';
                    $data['CreditCard']['ExpYear']          = 'xx';
                    $data['CreditCard']['SecurityCode']     = 'xxx';
                }
            }
        }
        return $request;
    }

    /**
     * @param $response
     * @return null|string
     */
    private function parseErrorMessage($response)
    {
        $acquirer = isset( $response->CreditCardTransactionResultCollection[0]->AcquirerName )
                        ? $response->CreditCardTransactionResultCollection[0]->AcquirerName
                        : null;

        $returnCode = isset( $response->CreditCardTransactionResultCollection[0]->AcquirerReturnCode )
                        ? $response->CreditCardTransactionResultCollection[0]->AcquirerReturnCode
                        : null;

        $message = $this->parseAcquirerReturn($acquirer, $returnCode);

        if($message && strlen($message) > 0){
            return $message;
        }

        if( isset($response->CreditCardTransactionResultCollection[0]->AcquirerMessage) ) {
            $message = explode("|", $response->CreditCardTransactionResultCollection[0]->AcquirerMessage);

            return isset($message[1])
                    ? $message[1]
                    : $response->CreditCardTransactionResultCollection[0]->AcquirerMessage;
        }

        if( isset($response->responseBody) ) {
            $responseBody = json_decode($response->responseBody);
            if (isset($responseBody->CreditCardTransactionResultCollection[0])) {
                $acquirerResponse = $responseBody->CreditCardTransactionResultCollection[0];
                $message = $this->parseAcquirerReturn(
                    $acquirerResponse->AcquirerName, $acquirerResponse->AcquirerReturnCode
                );
                if ($message) {
                    return $message;
                }

                $message = explode("|", $acquirerResponse->AcquirerMessage);
                if (isset($message[1]) && strlen($message[1])) {
                    return $message[1];
                }
            }
        }

        if (isset($response->errorCollection->ErrorItemCollection[0]->Description)) {
            return $response->errorCollection->ErrorItemCollection[0]->Description;
        }

        return null;
    }

    /**
     * @param $acquirerName
     * @param $acquirerReturnCode
     * @return null|string
     */
    private function parseAcquirerReturn($acquirerName, $acquirerReturnCode) {
        try {
            /**
             * @var $acquirer BaseAcquirer
             */
            $acquirer = app("\\Dlimars\\CreditCardReturns\\Acquirers\\$acquirerName");
            if($message = $acquirer->getMessageByCode($acquirerReturnCode)){
                return $message->getMessage();
            }

        } catch (\Exception $e) {}

        return null;
    }

    public function setFreightValue($freightValueInCents)
    {
        $this->shoppingCart->setFreightCostInCents( $freightValueInCents );

        return $this;
    }

    /**
     * @param Collection $items
     * @return mixed
     */
    public function setCartItems(Collection $items)
    {
        foreach ($items as $item) {
            $this->shoppingCart->addShoppingCartItem(
                (new ShoppingCartItem)
                    ->setItemReference($item->id)
                    ->setName($item->description)
                    ->setDescription($item->description)
                    ->setQuantity($item->qty)
                    ->setUnitCostInCents($this->getValueInCents($item->price))
                    ->setTotalCostInCents($this->getValueInCents($item->total))
            );
        }

        return $this;
    }

    private function getValueInCents($value)
    {
        return number_format($value, 2, '', '');
    }

    /**
     * @param $shoppingCart ShoppingCart
     */
    private function addShoppingCart($shoppingCart)
    {
        $customer = $this->customer;
        $shoppingCart->getDeliveryAddress()
                     ->setAddressType(AddressTypeEnum::SHIPPING)
                     ->setStreet($customer->address_street)
                     ->setNumber($customer->address_street_number)
                     ->setComplement($customer->address_street_complement)
                     ->setDistrict($customer->address_street_district)
                     ->setCity($customer->address_city)
                     ->setState($customer->uf)
                     ->setZipCode($customer->postcode)
                     ->setCountry(CountryEnum::BRAZIL);

        $this->saleRequest->addShoppingCart($shoppingCart);
    }

    /**
     * @return boolean
     */
    public function hasAntiFraud()
    {
        return (bool) $this->paymentSettings->mundipagg_merchantkey_antifraud;
    }

    /**
     * @inheritdoc
     */
    public function useAntiFraud($useAntiFraud = false)
    {
        if ($this->hasAntiFraud() && $useAntiFraud) {
            $this->api->setMerchantKey($this->paymentSettings->mundipagg_merchantkey_antifraud);
        } else {
            $this->api->setMerchantKey($this->paymentSettings->mundipagg_merchantkey);
        }

        return $this;
    }

    /**
     * @param $transactionKey
     * @param null $storeKey
     * @return mixed
     */
    public function findByKey($transactionKey, $storeKey = null)
    {
        return $this->find($transactionKey, false, $storeKey);
    }

    /**
     * @param $reference
     * @param null $storeKey
     * @return mixed
     */
    public function findByReference($reference, $storeKey = null)
    {
        return $this->find($reference, true, $storeKey);
    }

    private function find($referenceOrKey, $isReference, $storeKey)
    {
        if ($storeKey) {
            $this->api->setMerchantKey($storeKey);
        }

        if ($isReference) {
            return $this->callApi(function() use ($referenceOrKey){
                return $this->api->searchSaleByOrderReference($referenceOrKey);
            });
        } else {
            return $this->callApi(function() use ($referenceOrKey){
                return $this->api->searchSaleByOrderKey($referenceOrKey);
            });
        }
    }

    private function callApi(\Closure $closure)
    {
        $isSuccess = false;
        try {
            $response = $closure();

            $httpStatusCode = $response->isSuccess() ? 201 : 401;


            if ($response->isSuccess()) {
                $isSuccess = true;
                $message = 'Transação efetuada com sucesso';
            } else {
                $message = $this->parseErrorMessage( $response->getData() );

                if (!$message) {
                    $message = 'Transação não autorizada pela operadora do cartão, confira seus dados ou tente um novo cartão';
                } else {
                    $message = 'Transação não autorizada, motivo: ' . $message;
                }
            }
            $response = $response->getData();
        } catch (CreditCardError $response) {
            $httpStatusCode = 400;
            $message = $response->getMessage();
        } catch (ApiError $response) {
            $message = 'Transação não autorizada, motivo: ' . $this->parseErrorMessage( $response );
            $httpStatusCode = $response->httpStatusCode;
        } catch (\Exception $response) {
            $httpStatusCode = 500;
            $message = "Ocorreu um erro inesperado, por favor, tente novamente.";
        }

        return new PaymentResponse($isSuccess, $httpStatusCode, $message, [],
            json_encode($this->parseCreditCardNumberRequest($this->saleRequest->getData())),
            json_encode($response));
    }
}