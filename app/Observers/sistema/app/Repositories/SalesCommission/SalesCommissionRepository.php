<?php

namespace App\Repositories\SalesCommission;


use App\Entities\SalesCommission;
use Prettus\Repository\Eloquent\BaseRepository;

class SalesCommissionRepository extends BaseRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return SalesCommission::class;
    }
}