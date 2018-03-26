<?php

namespace App\Services\Gateways;

use App\Entities\Customer;
use App\Entities\PaymentSetting;
use App\Services\Gateways\Contracts\Boleto;
use Carbon\Carbon;
use Softr\Asaas\Adapter\GuzzleHttpAdapter;
use Softr\Asaas\Asaas as AsaasApi;

class Asaas implements Boleto
{
    /**
     * @var
     */
    private $api;

    private $customer;

    private $dueDate;

    private $value;

    /**
     * @var PaymentSetting
     */
    private $paymentSettings;

    /**
     * Asaas constructor.
     * @param PaymentSetting $settings
     */
    public function __construct(PaymentSetting $settings)
    {
        $this->paymentSettings = $settings;

        $this->api = new AsaasApi(
            new GuzzleHttpAdapter( $this->paymentSettings->asaas_apikey ), $this->paymentSettings->asaas_environment
        );
    }

    /**
     * @param Customer $customer
     * @return $this
     */
    public function setCustomer(Customer $customer)
    {
        $this->customer = $customer;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param Carbon $date
     * @return $this
     */
    public function setDueDate(Carbon $date = null)
    {
        $this->dueDate = $date;
        return $this;
    }

    /**
     * @return Carbon
     */
    public function getDueDate()
    {
        return $this->dueDate ?: Carbon::now()->addDays(
            $this->paymentSettings->asaas_days_expiration
        );
    }

    /**
     * @param float $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return float $value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return PaymentResponse
     */
    public function make()
    {
        try {
            $customer = $this->getAsaasCustomer();

            $append = auth()->user() ? " - com vendedor: " . auth()->user()->name : "";

            $request = [
                'customer' => $customer->id,
                'billingType' => 'BOLETO',
                'value' => $this->value,
                'interestValue' => number_format(0, 2, '.', ','),
                'dueDate' => $this->getDueDate()->format('d/m/Y'),
                'description' => $this->paymentSettings->asaas_boleto_description . $append,
                'status' => 'PENDING'
            ];

            $response = $this->api->payment()->create($request);
            $code = 200;
            $message = 'OK - Tudo ocorreu conforme o esperado';
        } catch (\Exception $e) {
            $code = $e->getCode();
            $message = $e->getMessage();
            $response = json_encode($message);
        } finally {
            return new PaymentResponse($code == 200, $code, $message, $response,
                json_encode(isset($request)?$request:[]), json_encode($response));
        }
    }

    /**
     * @return \Softr\Asaas\Entity\Customer
     * @throws \Exception
     */
    private function getAsaasCustomer()
    {
        try {
            // Caso não encontre, verifica se existe o cliente com o email
            if ($this->customer->email) {
                if($customer = $this->api->customer()->getByEmail($this->customer->email)) {
                    return $this->api->customer()->update($customer->id, $this->customerToAsaas($this->customer));
                }
            }

            // Caso contrario, cria um cliente novo
            return $this->createAsaasCustomer();
        } catch (\Exception $e){
            throw new \Exception("CPF Inválido");
        }
    }

    /**
     * @return \Softr\Asaas\Entity\Customer
     */
    private function createAsaasCustomer()
    {
        $asaasCustomer = $this->customerToAsaas( $this->customer );
        return $this->api->customer()->create( $asaasCustomer );
    }

    private function customerToAsaas(Customer $customer)
    {
        return [
            'name' => $customer->firstname . ' ' . $customer->lastname,
            'email' => $customer->email,
            'company' => '',
            'phone' => $customer->telephone,
            'mobilePhone' => $customer->telephone,
            'address' => $customer->address_street,
            'addressNumber' => $customer->address_street_number,
            'complement' => $customer->address_street_complement,
            'province' => $customer->address_street_district,
            'foreignCustomer' => false,
            'notificationDisabled' => false,
            'city' => $customer->address_city,
            'state' => $customer->address_state,
            'country' => 'Brasil',
            'postalCode' => $customer->postcode,
            'cpfCnpj' => str_replace(array('.', '-'), '', $customer->document_number),
            'personType' => 'FISICA'
        ];
    }

    public function findBillet($reference)
    {
        try {
            $response = $this->api->payment()->getById($reference);
            $code = 200;
            $message = 'OK';
            $paid = $response->status == "RECEIVED";
        } catch (\Exception $e) {
            $code = $e->getCode();
            $message = $e->getMessage();
            $response = $e;
            $paid = false;
        } finally {
            return new PaymentResponse($paid, $code, $message, $response,
                json_encode(isset($request)?$request:[]), json_encode($response));
        }
    }
}