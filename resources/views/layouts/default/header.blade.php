<nav>
    <ul id="locale" class="dropdown-content">
        <li><a href="{{ route('locale', 'en') }}">Eng</a></li>
        <li><a href="{{ route('locale','pt-br') }}">PT-Br</a></li>
    </ul>
    <div class="nav-wrapper">
        <div class="container">
            <a href="{{ route('app.index') }}" class="brand-logo">{{ __('LaraVue - Forum') }}</a>

            <ul class="right">
                <li><a href="#!" data-activates="locale" class="dropdown-button">{{ __('Language') }}</a></li>
            </ul>
        </div>
    </div>
</nav>
