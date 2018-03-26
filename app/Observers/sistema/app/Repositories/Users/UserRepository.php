<?php

namespace App\Repositories\Users;

use App\Entities\User;
use App\Repositories\BaseEloquentRepository;

class UserRepository extends BaseEloquentRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return User::class;
    }
}
