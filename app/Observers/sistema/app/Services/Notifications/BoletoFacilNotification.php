<?php

namespace App\Services\Notifications;

use App\Domain\OrderStatus;
use App\Entities\PaymentSetting;
use App\Repositories\Transaction\TransactionRepository;
use App\Services\Notifications\Contracts\NotificationContract;
use App\Support\SiteSettings;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;

class BoletoFacilNotification implements NotificationContract
{
    /**
     * @var SiteSettings
     */
    private $siteSettings;

    /**
     * @var PaymentSetting
     */
    private $paymentSettings;
    /**
     * @var TransactionRepository
     */
    private $transactionRepository;

    /**
     * BoletoFacilNotification constructor.
     * @param TransactionRepository $transactionRepository
     * @param SiteSettings $siteSettings
     */
    public function __construct(TransactionRepository $transactionRepository,
                                SiteSettings $siteSettings)
    {
        $this->siteSettings = $siteSettings;
        $this->paymentSettings = $siteSettings->getPaymentSettings();
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws \Exception
     */
    public function notify(Request $request)
    {
        $reference = $request->get("chargeReference");

        $transaction = $this->transactionRepository->findByField('pay_reference', $reference)->first();

        if ($order = $transaction->order) {
            if( !$order->isPaid() ) {
                $response = $this->doRequest("fetch-payment-details", [
                    "paymentToken" => $request->get("paymentToken")
                ]);

                if (isset($response->data->payment->amount)) {
                    $paidValue  = $response->data->payment->amount;
                    $totalOrder = $order->total_with_freight;

                    // Até 2 Reais de diferença para compensar Boleto
                    if ($totalOrder > $paidValue-2) {
                        $order->status = OrderStatus::APPROVED;
                        $order->save();
                        return true;
                    }
                }
                throw new Exception("Invalid Request, reference: $reference");
            }
            throw new Exception("Order Already Paid, reference: $reference");
        }
        throw new Exception("Order Not Found, reference: $reference");
    }

    /**
     * @param $uri
     * @param array $data
     * @return mixed|object
     */
    private function doRequest($uri, array $data = [])
    {
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

    /**
     * @param Exception $e
     * @return mixed
     */
    public function log(Exception $e)
    {
        // TODO: Implement log() method.
    }
}