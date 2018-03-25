@extends('layouts.default')

@section('content')
<div class="container">
    <div class="row">
        <div class="col s8">
            <div>
                <h3>Login</h3>
                <div class="row">
                    <form class="col s12" method="POST" action="{{ route('login') }}">
                        {{ csrf_field() }}
                        <div class="row">
                            <div class="input-field col s12">
                                <input id="email" type="email" name="email" value="{{ old('email') }}" class="validate">
                                <label for="email">{{ __('E-Mail Address') }}</label>
                                <span class="helper-text" data-error="wrong">{{ $errors->first('email') }}</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="input-field col s12">
                                <input id="password" type="password" name="password" class="validate">
                                <label for="password">{{ __('Password') }}</label>
                                <span class="helper-text" data-error="wrong">{{ $errors->first('password') }}</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col s12">
                                <input type="checkbox" id="remember" name="remember" class="filled-in" {{ old('remember') ? 'checked' : '' }}>
                                <label for="remember">{{ __('Remember me') }}</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col s12">
                                <button type="submit" class="btn btn-primary">
                                    Login
                                </button>

                                <a class="btn btn-link" href="{{ route('password.request') }}">
                                    {{__('Forgot Your Password?') }}
                                </a>
                                <a class="btn btn-link" href="{{ route('social.login', 'facebook') }}">
                                    {{ __('Login with Facebook') }}
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
