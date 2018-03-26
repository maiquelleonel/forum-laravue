<?php

namespace App\Entities;

use App\Domain\TransactionType;
use Illuminate\Database\Eloquent\Model;
use Laracasts\Presenter\PresentableTrait;
use App\Presenters\TransactionPresenter;
use App\Services\Payment\Response\Boleto;
use App\Services\Payment\Response\CreditCard;
use App\Services\Payment\Response\PagSeguro;

/**
 * @property Order order
 * @property string pay_reference
 * @SWG\Definition(
 *      definition="CreditCardPayment",
 *      required={
 *          "number", "cvv", "month", "year", "document_number", "name", "installments"
 *      },
 *      @SWG\Property(property="number", type="string", example="5555444433332222"),
 *      @SWG\Property(property="cvv", type="number", example="321"),
 *      @SWG\Property(property="month", type="number", example="02"),
 *      @SWG\Property(property="year", type="number", example="2044"),
 *      @SWG\Property(property="document_number", type="string", example="00000000191"),
 *      @SWG\Property(property="name", type="string", example="Luke Skywalker"),
 *      @SWG\Property(property="installments", type="integer", example="6")
 * )
 *
 * @SWG\Definition(
 *      definition="BoletoPayment",
 *      required={
 *          "document_number", "due_date"
 *      },
 *      @SWG\Property(property="document_number", type="string", example="76451803901"),
 *      @SWG\Property(property="due_date", type="string", example="2042-10-10", format="Y-m-d"),
 * )
 */
class Transaction extends Model
{
    use PresentableTrait;

    public $presenter = TransactionPresenter::class;

    protected $table = "transactions";

    protected $fillable = [
        'order_id',
        'pay_reference',
        'type',
        'response_json',
        'request_json',
        'user_id',
        'created_at',
        'updated_at'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function getResponseAttribute()
    {
        return json_decode( $this->response_json );
    }

    public function getRequestAttribute()
    {
        return json_decode( $this->request_json );
    }

    public function getTokenAttribute()
    {
        $response = json_decode($this->response_json);

        if (isset($response->CreditCardTransactionResultCollection[0]->CreditCard->InstantBuyKey)) {
            return $response->CreditCardTransactionResultCollection[0]->CreditCard->InstantBuyKey;
        }

        return null;
    }

    public function getAcquirerOrderKeyAttribute()
    {
        $response = json_decode($this->response_json);

        if (isset($response->OrderResult->OrderKey)) {
            return $response->OrderResult->OrderKey;
        }

        return null;
    }

    public function getTransaction()
    {
        switch ($this->type) {
            case TransactionType::BOLETO:
                return new Boleto($this);

            case TransactionType::PAGSEGURO:
                return new PagSeguro($this);

            case TransactionType::PAYPAL:
                return new PagSeguro($this);

        }

        return new CreditCard($this);
    }
}