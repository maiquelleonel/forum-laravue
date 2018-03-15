@extends('layouts.default')

@section('content')
    <div class="parallax-container">
        <div class="parallax">
            <img class="img" src="img/help.jpeg" />
        </div>
    </div>
    <div class="container">
        <h3>{{ __('Most recent threads') }}</h3>
        <threads
            title="{{ __('Threads') }}"
            thread="{{ __('Thread') }}"
            replies="{{ __('Replies') }}"
        >
            @include('layouts.default.preloader')
        </threads>
    </div>
@endsection

@section('scripts')
    <script src="/js/threads.js"></script>
@endsection
