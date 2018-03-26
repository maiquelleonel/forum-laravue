<?php

namespace App\Repositories\Transaction\Criteria;

use App\Domain\TransactionType;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class TransactionTypeCriteria implements CriteriaInterface
{
    /**
     * @var
     */
    private $transactionType;

    /**
     * TransactionTypeCriteria constructor.
     * @param $transactionType
     */
    public function __construct($transactionType)
    {
        $this->transactionType = $transactionType;
    }

    /**
     * Apply criteria in query repository
     *
     * @param $model
     * @param RepositoryInterface $repository
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository)
    {
        if ($this->transactionType) {
            switch(mb_strtoupper($this->transactionType))
            {
                case "CREDITCARD":
                    return $model->where("type", TransactionType::CARTAO);

                case "BOLETO":
                    return $model->where("type", TransactionType::BOLETO);

                case "PAGSEGURO":
                    return $model->where("type", TransactionType::PAGSEGURO);
            }
        }

        return $model;
    }
}
