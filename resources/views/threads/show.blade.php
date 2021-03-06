@extends('layouts.default')

@section('content')
    <div class="container">
        <h3>{{ $thread->title }}</h3>
        <div class="card grey lighten-4">
            <div class="card-content">
                <p>{{ $thread->body }}</p>
            </div>
            <div class="card-action">
                @can('update', $thread)
                    <a href="{{ route('thread.edit', $thread) }}">{{ __('Edit')}}</a>
                @endif
                <a href="{{ route('app.index') }}">{{ __('Back') }}</a>
            </div>
        </div>
        <replies
            replied="{{ __('replied') }}"
            reply="{{ __('Your reply')}}"
            your-answer="{{ __('Answer')}}"
            send="{{ __('Send')}}"
            thread-id="{{ $thread->id }}"
            highlight="{{ __('Hightlight reply') }}"
            thread-owner="{{ \Auth::user() ? \Auth::user()->can('update', $thread) : false }}"
            thread-closed="{{ $thread->closed }}"
        >
        </replies>
    </div>
@endsection

@section('scripts')
    <script src="/js/replies.js"></script>
@endsection
