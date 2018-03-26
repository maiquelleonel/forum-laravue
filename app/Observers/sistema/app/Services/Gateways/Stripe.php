<?php

namespace App\Services\Gateways;

use Dlimars\CreditCardReturns\Acquirers\BaseAcquirer;
use App\Entities\Customer;
use App\Services\Gateways\Contracts\CreditCard;
use App\Support\SiteSettings;
use Illuminate\Support\Collection;
    

class Stripe implements CreditCard
{
    private $customer;

    private $creditCard;

    private $totalInCents;

    private $saleRequest;
    
    private $installments;
    /**
     * @var ApiClient
     */
    private $api_key;
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
     * @var ShoppingCart
     */
    private $shoppingCart;
    
    private $is_rebuyer = false;
    

    public function __construct( SiteSettings $siteSettings )
    {
       $settings = $siteSettings->getPaymentSettings();
       $this->api_key = $settings->stripe_api_key;
    }

    /**
     * @inheritdoc
     */
    public function setTotal($totalInCents)
    {
        $this->totalInCents = $totalInCents;
        return $this;
    }

    public function setDescription($description){
        return $this;
    }

    /**
     * @param $id
     * @param $qty
     * @param $description
     * @param $unitValueInCents
     * @return $this
     */
    public function addItemCart($id, $qty, $description, $unitValueInCents){
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
        
        if(is_object($customer)){
            $this->customer = $customer;
        }else{
            $this->is_rebuyer = true;
            $this->custumer_id = $customer;
        }
        

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
            $this->creditCard = (Object) [
                'number'    => isset($card['number']) ? $card['number'] : null,
                'name'      => isset($card['name']) ? $card['name'] : null,
                'expiry'    => isset($card['month'], $card['year']) ? $card['month']."/".$card['year'] : null,
                'cvv'       => isset($card['cvv']) ? $card['cvv'] : null
            ];
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

            return $card;
        }
        return null;
    }


    /**
     * @inheritdoc
     */
    public function setIdentifier($identifier)
    {
        
        return $this;
    }

    public function setFreightValue($freightValueInCents){
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function makeAuthAndCapture()
    {
        return $this->makeTransaction();
    }
    /**
     * @inheritdoc
     */
    public function makeAuth()
    {
        return $this->makeTransaction();
    }

    /**
     * @inheritdoc
     */
    public function makeCapture()
    {
        return $this->makeTransaction();
    }

    /**
     * @inheritdoc
     */
    public function makeCancel( $acquirerOrderKey )
    {
        //$this->cancelRequest->setOrderKey($acquirerOrderKey);
        //return $this->makeTransaction();
        return $this;
    }

    /**
     * @param string $operation see CreditCardOperationEnum class
     * @return PaymentResponse
     */
    private function makeTransaction()
    {
        
        $stripe_statuses = [
            'succeeded' => 'Transação efetuada com sucesso',
            'pending'   => 'Transação pendente. Aguarde!',
            'failed'    => 'Transação não autorizada pela operadora do cartão',
        ];

        \Stripe\Stripe::setApiKey(
            $this->stripe_api_key
        );
        
        $card = $this->getCreditCard();
        //dd($card);
        list($card_exp_month, $card_exp_year) = explode('/', $card->expiry );
        //create a token
        $token = \Stripe\Token::create([
            'card' => [
                'number'    => $card->number  ,
                'exp_month' => $card_exp_month,
                'exp_year'  => $card_exp_year ,
                'cvc'       => $card->cvv     ,
            ],
        ]);

        if($this->is_rebuyer ){

            $customer = \Stripe\Costumer::retrieve( $this->custumer_id );

        } else {

            $customer = \Stripe\Customer::create([
                'email'  => $this->customer->email,
                'source' => $token,
            ]);
        }

        //create a charge
        $charge = \Stripe\Charge::create([
            "amount"   => $this->totalInCents , 
            "currency" => "usd"               , 
            "customer" => $customer->id       ,
        ]);    

        $message = $stripe_statuses[ $charge->status ];

        $httpStatusCode = ($charge->status == 'succeeded' )? 201 : 401;
        

        return new PaymentResponse($httpStatusCode === 201, $httpStatusCode, $message, [],
            json_encode([
                'customer_id' => $customer->id,
                'charge_id'   => $charge->id  ,
            ]),
            json_encode($charge)
        );
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
     * @param Collection $items
     * @return $this
     */
    public function setCartItems(Collection $items)
    {
        // TODO: Implement setCartItems() method.
    }

    /**
     * @return boolean
     */
    public function hasAntiFraud()
    {
        // TODO: Implement hasAntiFraud() method.
    }

    /**
     * @param bool $useAntiFraud
     * @return $this
     */
    public function useAntiFraud($useAntiFraud = false)
    {
        // TODO: Implement useAntiFraud() method.
    }

    /**
     * @param $transactionKey
     * @param null $storeKey
     * @return PaymentResponse
     */
    public function findByKey($transactionKey, $storeKey = null)
    {
        // TODO: Implement findByKey() method.
    }

    /**
     * @param $reference
     * @param null $storeKey
     * @return PaymentResponse
     */
    public function findByReference($reference, $storeKey = null)
    {
        // TODO: Implement findByReference() method.
    }
}