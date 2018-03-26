<?php

namespace App\Repositories\Customers;

use App\Domain\BundleCategory;
use App\Domain\OrderStatus;
use App\Entities\Customer;
use App\Repositories\BaseEloquentRepository;

class CustomerRepository extends BaseEloquentRepository
{
    protected $fieldSearchable = [
        'firstname' =>'like',
        'lastname'  => 'like',
        'email'     =>'like'
    ];

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Customer::class;
    }

    /**
     * Clientes que não possuem nenhuma order, apenas fizeram o cadastro
     * @param Customer $customer
     * @return bool
     */
    public function customerHasOrder(Customer $customer)
    {
        return $customer->orders->count() > 0;
    }

    /**
     * Clientes que tem pagamento aprovado
     * @param Customer $customer
     * @return bool
     */
    public function customerHasApprovedOrder(Customer $customer)
    {
        $paidOrder = $customer->orders->first(function ($key, $order) {
            return $order->isPaid();
        });

        return (bool) $paidOrder;
    }

    /**
     * Clientes que compraram upsell
     * @param Customer $customer
     * @return bool
     */
    public function customerHasUpsellOrder(Customer $customer)
    {
        $upsellOrder = $customer->orders->first(function ($index, $order) {
            if ($order->isPaid()) {
                if ($order->itemsBundle->count() > 0) {
                    $itemBundle = $order->itemsBundle->first(function ($index, $itemBundle) {
                        return $itemBundle->category == BundleCategory::UPSELL;
                    });
                    return (bool) $itemBundle;
                }
            }

            return false;
        });

        return (bool) $upsellOrder;
    }

    /**
     * Clientes que solicitaram pagamento por boleto
     * @param Customer $customer
     * @return bool
     */
    public function customerHasPendingOrder(Customer $customer)
    {
        $pendingOrder = $customer->orders->first(function ($key, $order) {
            return $order->status == OrderStatus::PENDING;
        });

        return (bool) $pendingOrder;
    }
}
