<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="stylesheet" href="/css/app.css">
    <title>LaraVue Fórum</title>
</head>
<body>
    <header>
        @include('layouts.default.header')
    </header>
    <main>
        <section>
            <div id="app">
                @yield('content')
            </div>
        </section>
    </main>

    @include('layouts.default.footer')

    @component('layouts.default.body_scripts')
        @yield('scripts')
    @endcomponent
</body>
</html>
