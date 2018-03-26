<?php
/**
 * Created by PhpStorm.
 * User: dlima
 * Date: 3/11/16
 * Time: 2:32 PM
 */

namespace App\Repositories\Orders\Criteria;


use Carbon\Carbon;
use Illuminate\Http\Request;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class CanceledsCriteria implements CriteriaInterface
{
    /**
     * @var Request
     */
    private $request;
    /**
     * @var null
     */
    private $paymentType;

    /**
     * AprovedsCriteria constructor.
     * @param Request $request
     * @param null $paymentType
     */
    public function __construct(Request $request, $paymentType = null)
    {
        $this->request = $request;
        $this->paymentType = $paymentType;
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
        $day = $this->request->input('day');

        if ($day instanceof Carbon) {
            $day = $day->format('Y-m-d');
        }

        if ($this->paymentType) {
            $model = $model->where('payment_type_collection', $this->paymentType);
        }

        return $model->whereIn('status', ['cancelado', 'retentativa', 'pendente'])
                     ->where(function($query) use ($day) {
                        $query->where('created_at', 'like', "$day%")
                              ->orWhere('updated_at', 'like', "$day%");
                     });
    }
}