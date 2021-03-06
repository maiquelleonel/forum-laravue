<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
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
    <div id="loader">
        <loader />
    </div>
    @include('layouts.default.footer')

    @component('layouts.default.body_scripts')
        @yield('scripts')
    @endcomponent
</body>
</html>
