<nav>
    <ul id="locale" class="dropdown-content">
        <li><a href="{{ route('locale', 'en') }}">Eng</a></li>
        <li><a href="{{ route('locale','pt-br') }}">PT-Br</a></li>
    </ul>
    @if (\Auth::user())
        <ul id="user" class="dropdown-content">
            <li><a href="/profile">{{ __('Profile') }}</a></li>
            <li>
                <a href="{{ route('logout') }}"
                    onclick="event.preventDefault();
                             document.getElementById('logout-form').submit();">
                    Logout
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    {{ csrf_field() }}
                </form>
            </li>
        </ul>
    @endif
    <div class="nav-wrapper">
        <div class="container">
            <a href="{{ route('app.index') }}" class="brand-logo">{{ __('LaraVue - Forum') }}</a>

            <ul class="right">
                <li><a href="#!" data-activates="locale" class="dropdown-button">{{ __('Language') }}</a></li>
                @if (\Auth::user())
                    <li><a href="#!" data-activates="user" class="dropdown-button">{{ \Auth::user()->name }}</a></li>
                @else
                    <li><a href="{{ route('login') }}">Login</a></li>
                    <li><a href="{{ route('register') }}">{{ __('Sign Up') }}</a></li>
                @endif
            </ul>
        </div>
    </div>
</nav>
