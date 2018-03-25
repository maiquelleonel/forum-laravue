<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\User;
use App\SocialAuth;

class SocialAuthController extends Controller
{
    public function redirect($provider)
    {
        return \Socialite::driver($provider)->redirect();
    }

    public function callback($provider)
    {
        $social_user = \Socialite::driver($provider)->user();
        $account = SocialAuth::where([
            'provider'  => $provider,
            'social_id' => $social_user->id,
        ])->first();

        if ($account) {
            //auth
            auth()->login($account->user);
            return redirect()->to(route('app.index'));
        }

        $user = User::where('email', $social_user->email)->first();
        if ($user) {
            //not auth
            return redirect()->to(route('app.index'));
        }

        $new_user           = new User;
        $new_user->name     = $social_user->name;
        $new_user->email    = $social_user->email;
        $new_user->password = md5(str_random(128));
        $new_user->save();

        $account            = new SocialAuth;
        $account->provider  = $provider;
        $account->social_id = $social_user->id;
        $account->user()->associate($new_user);
        $account->save();

        auth()->login($new_user);
    }
}
