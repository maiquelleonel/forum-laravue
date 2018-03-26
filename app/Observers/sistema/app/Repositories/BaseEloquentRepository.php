<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;

abstract class BaseEloquentRepository extends BaseRepository
{
    /**
     * Get total of registers in the table
     * @return integer total of registers
     */
    public function count()
    {
        $this->applyCriteria();
        $this->applyScope();

        $result = $this->model->count();

        $this->resetModel();
        $this->resetScope();

        return $result;

    }
}