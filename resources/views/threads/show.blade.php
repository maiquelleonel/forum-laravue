@extends('layouts.default')

@section('content')
    <div class="container">
        <h3>{{ $thread->title }}</h3>
        <div class="card grey lighten-4">
            <div class="card-content">
                <p>{{ $thread->body }}</p>
            </div>
            <div class="card-action">
                @if (\Auth::user() and \Auth::user()->can('update', $thread))
                    <a href="{{ route('thread.edit', $thread) }}">{{ __('Edit')}}</a>
                @endif
                <a href="{{ route('app.index') }}">{{ __('Back') }}</a>
            </div>
        </div>
        <replies
            replied="{{ __('replied') }}"
            reply="{{ __('Your reply')}}"
            your-answer="{{ __('Answer')}}"
            send="{{ __('Enviar')}}"
            thread-id="{{ $thread->id }}"
        >
        </replies>
    </div>
@endsection

@section('scripts')
    <script src="/js/replies.js"></script>
@endsection
