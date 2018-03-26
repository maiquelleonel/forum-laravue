<?php

namespace App\Http\Controllers\Admin;

use App\Entities\ConfigCommissionGroup;
use App\Entities\User;
use App\Repositories\Currency\CurrencyRepository;
use App\Repositories\Users\Criteria\UserDataCriteria;
use App\Repositories\Users\UserRepository;
use App\Support\SiteSettings;
use Artesaos\Defender\Role;
use Carbon\Carbon;
use App\Http\Requests\Admin\Request;

class UserController extends Controller
{
    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * UserController constructor.
     * @param SiteSettings $siteSettings
     * @param UserRepository $userRepository
     */
    public function __construct(SiteSettings $siteSettings, UserRepository $userRepository)
    {
        parent::__construct($siteSettings);
        $this->userRepository = $userRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $activeCounter = User::count();
        $inactiveCounter = User::onlyTrashed()->count();

        $users = $this->userRepository
            ->pushCriteria(new UserDataCriteria($request))
            ->paginate(10);

        return view('admin.pages.users.index', compact('users', 'activeCounter', 'inactiveCounter'));
    }

    /**
     * Show form to edit user
     * @param Request $request
     * @param $userId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(Request $request, $userId)
    {
        $user = User::withTrashed()->find($userId);
        $roles = Role::all();
        $commissionGroups = [null=>"Nenhum"] + ConfigCommissionGroup::lists('name', 'id')->toArray();
        $currencies       = app(CurrencyRepository::class)->toSelectArray();

        return view('admin.pages.users.edit', compact('user', 'roles', 'commissionGroups', 'currencies'));
    }

    /**
     * Show form to create user
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create(Request $request)
    {
        $roles = Role::all();
        $commissionGroups = [null=>"Nenhum"] + ConfigCommissionGroup::lists('name', 'id')->toArray();
        $currencies       = app(CurrencyRepository::class)->toSelectArray();

        return view('admin.pages.users.create', compact('roles', 'commissionGroups', 'currencies'));
    }

    /**
     * Store new User
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory
     */
    public function store(Request $request)
    {
        $data = $request->only('name', 'email', 'affiliate_id', 'locale', 'currency_id', 'evolux_login');

        if ($request->has('config_commission_group_id')) {
            $data['config_commission_group_id'] = $request->get('config_commission_group_id');
        }

        if (!isset($data['affiliate_id']) || empty($data['affiliate_id'])) {
            $data['affiliate_id'] = null;
        }

        if ($pass = $request->input('new_password')) {
            $data['password'] = bcrypt($pass);
        }

        switch ($request->input('status')) {
            case "1":
                $data["deleted_at"] = null;
                break;

            case "0":
                $data["deleted_at"] = Carbon::now();
                break;
        }

        $user = new User;
        $user->fill($data);
        $user->new_password = $request->input('new_password');
        $user->save();

        $user->syncRoles($request->get("roles", []));

        return redirect()->route('admin:users.index');
    }

    /**
     * Update User
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory
     */
    public function update(Request $request, $userId)
    {
        $user = User::withTrashed()->find($userId);

        $data = $request->only('name', 'email', 'affiliate_id', 'locale', 'currency_id', 'evolux_login');

        $data['config_commission_group_id'] = null;
        if ($request->has('config_commission_group_id')) {
            $data['config_commission_group_id'] = $request->get('config_commission_group_id');
        }

        if (!isset($data['affiliate_id']) || empty($data['affiliate_id'])) {
            $data['affiliate_id'] = null;
        }

        if ($pass = $request->input('new_password')) {
            $data['password'] = bcrypt($pass);
            $user->new_password = $request->input('new_password');
        }

        switch ($request->input('status')) {
            case "1":
                $data["deleted_at"] = null;
                break;

            case "0":
                $data["deleted_at"] = Carbon::now();
                break;
        }

        $user->update($data);

        $user->syncRoles($request->get("roles", []));

        return redirect()->route('admin:users.index');
    }
}
