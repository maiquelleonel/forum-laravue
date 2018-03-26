<?php

namespace App\Observers;

use App\Domain\TransactionType;
use App\Entities\Transaction;
use App\Events\OrderBilletCreated;

class TransactionObserver
{
    public function created(Transaction $transaction)
    {
        if ($transaction->type == TransactionType::BOLETO) {
            event(new OrderBilletCreated($transaction->order, $transaction));
        }
    }

    public function saving(Transaction $transaction)
    {
        if ($user = auth()->user()) {
            $transaction->user_id = $user->id;
        }
    }
}
