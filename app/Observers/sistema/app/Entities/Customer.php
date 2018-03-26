<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Laracasts\Presenter\PresentableTrait;
use App\Presenters\CustomerPresenter;
use App\Services\Payment\Response\CreditCard;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * @SWG\Definition(
 *     definition="Customer",
 *     required={
 *          "firstname",
 *          "lastname",
 *          "email",
 *          "telephone",
 *          "postcode",
 *          "address_street",
 *          "address_street_number",
 *          "address_street_district",
 *          "address_city",
 *          "address_state"
 *     },
 *     @SWG\Property(
 *          property="customer_id", type="integer", description="Customer's ID", example=""
 *     ),
 *     @SWG\Property(
 *          property="firstname", type="string", description="Customer's first name", example="Luke"
 *     ),
 *     @SWG\Property(
 *          property="lastname", type="string", description="Customer's last name", example="Skywalker"
 *     ),
 *     @SWG\Property(
 *          property="email", type="string", description="Customer's email", example="luke@moseisley.com"
 *     ),
 *     @SWG\Property(
 *          property="telephone", type="string", description="Customer's telephone", example="(51) 9999.9999"
 *     ),
 *     @SWG\Property(
 *          property="postcode", type="string", description="Customer's postcode", example="94000999"
 *     ),
 *     @SWG\Property(
 *          property="address_street", type="string", description="Customer's street name", example="Street Mos Eisley"
 *     ),
 *     @SWG\Property(
 *          property="address_street_number", type="string",
 *          description="Customer's address street number", example="10|SN|s/n"
 *     ),
 *     @SWG\Property(
 *          property="address_street_district", type="string",
 *          description="Customer's address street district", example="Center Tatooine"
 *     ),
 *     @SWG\Property(
 *          property="address_city", type="string", description="Customer's city", example="Pirate City"
 *     ),
 *     @SWG\Property(
 *          property="address_state", type="string", description="Customer's state", example="RS|SP|Tatooine"
 *     ),
 *     @SWG\Property(
 *          property="custom_txt", type="string", description="Customer's custom text description"
 *     )
 * )
 * @SWG\Definition(
 *     definition="ArrayOfCustomers",
 *     type = "array",
 *     @SWG\Items(ref = "#/definitions/Customer")
 * )
 * @property integer site_id
 */
class Customer extends Model implements AuditableContract
{
    use PresentableTrait, Auditable;

    protected $table = "customers";

    protected $presenter = CustomerPresenter::class;

    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'telephone',
        'postcode',
        'address_street',
        'address_street_number',
        'address_street_complement',
        'address_street_district',
        'address_city',
        'address_state',
        'document_number',
        'site_id',
        'click_id',
        'source',
        'custom_txt',
        'ip',
        'user_agent',
        'device',
    ];

    protected $hidden = [
        'hash',
        "device",
        "user_agent",
        "source",
        "click_id",
        "site_id",
        "uf"
    ];

    protected $appends = [
        'uf'
    ];

    public function getRouteKeyName()
    {
        return 'hash';
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function emailCampaignContact()
    {
        return $this->hasOne(EmailCampaignContact::class);
    }

    public function visits()
    {
        return $this->hasMany(PageVisit::class, "customer_id");
    }

    public function lastVisit()
    {
        return $this->hasOne(PageVisit::class, "customer_id")
                    ->orderBy('created_at', 'desc');
    }

    public function firstVisit()
    {
        return $this->hasOne(PageVisit::class, "customer_id")
                    ->orderBy('created_at', 'asc');
    }

    public function getUfAttribute()
    {
        return $this->address_state;
    }

    public function getPlainDocumentNumberAttribute()
    {
        return str_ireplace(["-", " ", "."], "", $this->document_number);
    }

    public function getFormattedTelephoneAttribute()
    {
        $phone = str_ireplace([' ', '(', ')', '-', '_'], "", $this->telephone);
        return "(" . substr_replace($phone, ")", 2, 0);
    }

    public function creditCardTransactions()
    {
        return $this->hasManyThrough(Transaction::class, Order::class)
                    ->orderBy('id', 'desc')
                    ->where('response_json', 'LIKE', '%InstantBuyKey%');
    }

    public function getCreditCards()
    {
        $creditCards = [];

        foreach ($this->creditCardTransactions as $transaction) {
            $transaction = $transaction->getTransaction();
            /**
             * @var $transaction CreditCard
             */
            if (($card = $transaction->getCardNumber()) && ($name = $transaction->getHolderName())) {
                $creditCards["$card-$name"] = $transaction;
            }
        }

        return $creditCards;
    }

    public function fullName()
    {
        return join(' ', [$this->firstname, $this->lastname]);
    }
}
