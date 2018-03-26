<?php

namespace App\Http\Controllers\Admin;

use App\Entities\User;
use Artesaos\Defender\Role;
use Carbon\Carbon;
use App\Http\Requests\Admin\Request;

use App\Http\Requests;

class ProfileController extends Controller
{
    /**
     * Show form to edit user
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit()
    {
        $user = auth()->user();
        $roles = [];

        return view('admin.pages.profile.edit', compact('user', 'roles'));
    }

    /**
     * Update User
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory
     */
    public function update(Request $request)
    {
        $user = $user = auth()->user();

        $data = $request->only('name', 'email', 'locale');

        if ($pass = $request->input('new_password')) {
            $data['password'] = bcrypt($pass);
            $user->new_password = $request->input('new_password');
        }

        $user->update($data);

        session()->flash('success', trans("message.update_success"));

        return redirect()->back();
    }
}
