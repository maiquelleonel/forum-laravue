<?php

namespace App\Services\Notifications;

use App\Domain\OrderStatus;
use App\Entities\Order;
use App\Entities\PaymentSetting;
use App\Repositories\Orders\OrderRepository;
use App\Repositories\Transaction\TransactionRepository;
use App\Services\Notifications\Contracts\NotificationContract;
use App\Support\SiteSettings;
use Exception;
use Illuminate\Http\Request;
use PHPSC\PagSeguro\Credentials;
use PHPSC\PagSeguro\Environments\Production;
use PHPSC\PagSeguro\Environments\Sandbox;
use PHPSC\PagSeguro\Purchases\Details;
use PHPSC\PagSeguro\Purchases\Transactions\Locator;

class PagSeguroNotification implements NotificationContract
{
    /**
     * @var TransactionRepository
     */
    private $transactionRepository;
    /**
     * @var OrderRepository
     */
    private $orderRepository;
    /**
     * @var SiteSettings
     */
    private $settings;

    /**
     * AsaasNotification constructor.
     * @param SiteSettings $settings
     * @param OrderRepository $orderRepository
     * @param TransactionRepository $transactionRepository
     */
    public function __construct(SiteSettings $settings,
                                OrderRepository $orderRepository,
                                TransactionRepository $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
        $this->orderRepository = $orderRepository;
        $this->settings = $settings;
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws Exception
     */
    public function notify(Request $request)
    {
        /**
         * @var $details Details
         * @var $order Order
         */

        $credentials = $this->getAllCredentials();

        foreach($credentials as $credential) {

            $locator = new Locator($credential);

            try {

                if ($notification = $request->get('notificationCode')) {
                    $purchase = $locator->getByNotification($notification);
                } else {
                    $purchase = $locator->getByCode($request->get('transactionCode'));
                }

                $details = $purchase->getDetails();
                $reference = $details->getReference();

                if ($purchase->isPaid() && $reference && !empty($reference)) {

                    $transaction = $this->transactionRepository->findByField('pay_reference', $reference)->first();

                    if ($order = $transaction->order) {
                        if (!$order->isPaid()) {
                            $order->update([
                                'status' => OrderStatus::APPROVED
                            ]);
                            return true;
                        }
                    }
                }
            } catch (\Exception $e) {

            }
        }

        return false;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    private function getAllCredentials()
    {
        $settings = PaymentSetting::whereNotNull("pagseguro_email")
                                    ->whereNotNull("pagseguro_token")
                                    ->where("pagseguro_environment", "production")
                                    ->groupBy("pagseguro_email", "pagseguro_token")
                                    ->get();

        $credentials = collect();

        foreach ($settings as $setting) {
            $credentials->push(new Credentials(
                $setting->pagseguro_email,
                $setting->pagseguro_token,
                new Production()
            ));
        }

        return $credentials;
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