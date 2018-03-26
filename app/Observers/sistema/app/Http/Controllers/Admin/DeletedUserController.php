<?php
namespace App\Http\Controllers\Admin;

use App\Entities\User;
use App\Http\Requests\Admin\Request;

use App\Http\Requests;
use App\Repositories\Users\Criteria\UserDataCriteria;

class DeletedUserController extends UserController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $activeCounter = User::count();
        $inactiveCounter = User::onlyTrashed()->count();

        $users = $this->userRepository
            ->scopeQuery(function($query){
                return $query->onlyTrashed();
            })
            ->pushCriteria(new UserDataCriteria($request))
            ->paginate(10);

        return view('admin.pages.users.index', compact('users', 'activeCounter', 'inactiveCounter'));
    }
}
