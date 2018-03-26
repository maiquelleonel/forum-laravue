<?php

namespace App\Repositories\Transaction;

use App\Entities\Site;
use App\Repositories\Criterias\AfterLastUpdateTimeCriteria;
use App\Repositories\Criterias\DoesntHaveRelationCriteria;
use App\Repositories\Transaction\Criteria\TransactionTypeCriteria;
use App\Services\Gateways\PaymentResponse;
use App\Support\SiteSettings;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use App\Entities\Transaction;
use App\Services\Gateways\Contracts\CreditCard;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Class BundleRepositoryEloquent
 * @package namespace App\Repositories;
 */
class TransactionRepository extends BaseRepository
{

    /**
     * @var SiteSettings
     */
    private $siteSettings;

    /**
     * TransactionRepository constructor.
     * @param Application $app
     * @param SiteSettings $siteSettings
     */
    public function __construct(Application $app, SiteSettings $siteSettings)
    {
        parent::__construct($app);
        $this->siteSettings = $siteSettings;
    }

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Transaction::class;
    }

    public function getBoleto($orderId)
    {
        $transactions = $this->model->where('order_id', $orderId)->orderBy('id', 'desc')->get();

        foreach ($transactions as $transaction) {
            if (isset($transaction->response_json->boletoUrl)) {
                return $transaction->response_json->boletoUrl;
            }
        }
    }

    /**
     * @param $id
     * @return \App\Services\Gateways\PaymentResponse
     */
    public function capture($id)
    {
        // @todo refactor by Specific Gateway
        return;
        $transaction = $this->find($id);

        $reference = isset($transaction->response_json->Order->OrderReference)
            ? $transaction->response_json->Order->OrderReference
            : $transaction->pay_reference;

        if (isset($transaction->response_json->OrderResult->OrderKey)) {
            $response = $this->creditCardService->makeCapture(
                $transaction->response_json->OrderResult->OrderKey
            );
        }

        if (!isset($response)) {
            $response = new PaymentResponse(false, 1, "OrderKey não encontrada", [], [], []);
        }

        $this->registerTransaction($response, $transaction->order_id, $reference);

        return $response;
    }

    /**
     * @param $id
     * @return \App\Services\Gateways\PaymentResponse
     */
    public function refund($id)
    {
        // @todo refactor by Specific Gateway
        return;
        $transaction = $this->find($id);

        if (isset($transaction->response_json->OrderResult->OrderKey)) {
            $response = $this->creditCardService->makeCancel(
                $transaction->response_json->OrderResult->OrderKey
            );
        }

        if (!isset($response)) {
            $response = new PaymentResponse(false, 1, "OrderKey não encontrada", [], [], []);
        }

        $this->registerTransaction($response, $transaction->order_id, $transaction->pay_reference);

        return $response;
    }

    /**
     * @param PaymentResponse $transaction
     * @param $orderId
     * @param $orderReference
     * @return \App\Entities\Transaction
     */
    private function registerTransaction(PaymentResponse $transaction, $orderId, $orderReference)
    {
        return $this->create([
            'order_id'      => $orderId,
            'pay_reference' => $orderReference,
            'request_json'  => $transaction->getTransactionRequest(),
            'response_json' => $transaction->getTransactionResponse()
        ]);
    }
}
