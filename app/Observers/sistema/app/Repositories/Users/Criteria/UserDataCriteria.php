<?php

namespace App\Repositories\Users\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class UserDataCriteria implements CriteriaInterface
{
    /**
     * @var Request
     */
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Apply criteria in query repository
     *
     * @param $model Builder
     * @param RepositoryInterface $repository
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository)
    {
        $searchable = $this->getFieldsSearchable();
        $fields     = $this->request->only(array_keys($searchable));
        foreach($fields as $fieldName=>$value) {
            if ($value) {
                $model = $model->where(
                    \DB::raw($searchable[$fieldName]), "LIKE", "%". str_ireplace(" ", "%", $value) ."%"
                );
            }
        }
        return $model;
    }

    /**
     * Get Fields to search
     *
     * @return array
     */
    public function getFieldsSearchable()
    {
        return [
            "id"        => 'id',
            "name"      => 'name',
            "email"     => 'email',
        ];
    }
}