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

class TotalDayCriteria implements CriteriaInterface
{
    /**
     * @var Request
     */
    private $request;

    /**
     * AprovedsCriteria constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
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

        return $model->where('created_at', 'like', "$day%");
    }
}