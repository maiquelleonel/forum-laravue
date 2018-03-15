@extends('layouts.default')

@section('content')
    <div class="container">
        <h3>{{ $thread->title }}</h3>
        <div class="card grey lighten-4">
            <div class="card-content">
                <p>{{ $thread->body }}</p>
            </div>
        </div>
        <replies
            replied="{{ __('replied') }}"
            reply="{{ __('Your reply')}}"
            your-answer="{{ __('Answer')}}"
            send="{{ __('Enviar')}}"
        >
            @include('layouts.default.preloader')
        </replies>
    </div>
@endsection

@section('scripts')
    <script src="/js/replies.js"></script>
@endsection
