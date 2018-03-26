<?php
/**
 * Created by PhpStorm.
 * User: dlima
 * Date: 5/23/17
 * Time: 14:26
 */

namespace App\Services\Gateways;


use App\Entities\Customer;
use App\Entities\PaymentSetting;
use App\Services\Gateways\Contracts\Boleto;
use App\Support\SiteSettings;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class BoletoFacil implements Boleto
{
    /**
     * @var Customer
     */
    private $customer;

    /**
     * @var Carbon
     */
    private $dueDate;

    /**
     * @var float
     */
    private $value;

    /**
     * @var SiteSettings
     */
    private $settings;

    /**
     * @var PaymentSetting
     */
    private $paymentSettings;

    /**
     * BoletoFacil constructor.
     * @param SiteSettings $settings
     */
    public function __construct(SiteSettings $settings)
    {
        $this->settings = $settings;
        $this->paymentSettings = $settings->getPaymentSettings();
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
     * @return Customer $customer
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
            $this->paymentSettings->boleto_facil_days_expiration
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
        $append = auth()->user() ? " - com vendedor: " . auth()->user()->name : "";

        $data = [
            'description'   => $this->paymentSettings->boleto_facil_description . $append,
            'amount'        => $this->getValue(),
            'dueDate'       => $this->getDueDate()->format('d/m/Y'),

            'payerName'     => $this->customer->firstname . ' ' . $this->customer->lastname,
            'payerCpfCnpj'  => $this->customer->document_number,
            'payerEmail'    => $this->customer->email,
            'reference'     => mb_strtoupper(wordwrap(md5(uniqid(rand(), true)), 8, '-', true)),
            'notifyPayer'   => true
        ];

        $response = $this->doRequest("issue-charge", $data);

        $message = !$response->success ? "Falha ao emitir boleto: " . $response->errorMessage : "Boleto Emitido com Sucesso!";

        return new PaymentResponse($response->success, 200, $message, null, json_encode($data), json_encode($response));
    }

    /**
     * @param $uri
     * @param array $data
     * @return mixed|object
     */
    private function doRequest($uri, array $data = [])
    {
        $data['token'] = $this->paymentSettings->boleto_facil_apikey;

        try {
            $client = new Client(['base_uri' => $this->getUrl()]);
            return json_decode( $client->post($uri, ['form_params' => $data])->getBody() );
        } catch (RequestException $e) {
            return json_decode( $e->getResponse()->getBody() );
        } catch (\Exception $e) {
            return (object) [
                'status' => false, 'errorMessage' => $e->getMessage()
            ];
        }
    }

    /**
     * @return string
     */
    private function getUrl()
    {
        return $this->paymentSettings->boleto_facil_environment != "production"
                ? "https://sandbox.boletobancario.com/boletofacil/integration/api/v1/"
                : "https://www.boletobancario.com/boletofacil/integration/api/v1/";
    }

    public function findBillet($reference)
    {
        // TODO: Implement findBillet() method.
    }
}