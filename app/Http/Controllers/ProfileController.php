<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        $user = \Auth::user();
        return view('profile.form', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
        ]);

        $user        = \Auth::user();
        $user->name  = $request->input('name');
        $user->email = $request->input('email');
        $user->photo = $request->file('photo');

        if ($request->input('password')) {
            $this->validate($request, [
                'password' => 'string|min:6|confirmed',
            ]);
            $user->password = bcrypt($request->password);
        }

        $user->save();

        return back();
    }
}
