<?php

namespace App\Services\Notifications;

use App\Domain\AsaasWebhook;
use App\Domain\OrderStatus;
use App\Services\Notifications\Contracts\NotificationContract;
use Exception;
use Illuminate\Http\Request;
use App\Repositories\Orders\OrderRepository;
use App\Repositories\Transaction\TransactionRepository;

class AsaasNotification implements NotificationContract
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
     * AsaasNotification constructor.
     * @param OrderRepository $orderRepository
     * @param TransactionRepository $transactionRepository
     */
    public function __construct(OrderRepository $orderRepository, TransactionRepository $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws Exception
     */
    public function notify(Request $request)
    {
        $data = $this->getData( $request );

        if ($data->id && !empty($data->id) && $data->event) {

            $transaction = $this->transactionRepository->findByField('pay_reference', $data->id)->first();

            // Verifica se existe uma order para a transação selecionada
            if($transaction && ($order = $transaction->order)) {

                // Verifica se o pedido já está aprovado|integrado|autorizado
                if(!$order->isPaid()){

                    // Verifica se o tipo de notificação é de pagamento recebido
                    if(in_array($data->event, [ AsaasWebhook::PAYMENT_CONFIRMED, AsaasWebhook::PAYMENT_RECEIVED ]) ) {
                        $order->status = OrderStatus::APPROVED;
                        $order->save();
                        return true;
                    }
                    return false;
                }
                throw new Exception("(Asaas) {$order->id} Order already approved");
            }

            throw new Exception("(Asaas) Order not found:" . $data->id);
        }
        throw new Exception("(Asaas) Malformed request:" . $request->input('data'));
    }

    /**
     * @param Exception $e
     * @return mixed
     */
    public function log(Exception $e)
    {
        // TODO: Implement log() method.
    }

    /**
     * @param Request $request
     * @return object
     */
    private function getData(Request $request)
    {
        $id = null;
        $event = null;

        if( $request->json("payment.id") ) {

            $id     = $request->json("payment.id");
            $event  = $request->json("event");

        } else if ($request->input('data')) {

            $data   = json_decode($request->input('data'));
            $id     = $data->payment->id;
            $event  = $data->event;

        }

        return (object) [
            "id"    => $id,
            "event" => $event
        ];
    }
}