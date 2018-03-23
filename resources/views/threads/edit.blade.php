@extends('layouts.default')

@section('content')
    <div class="container">
        <h3>{{ $thread->title }}</h3>
        <div class="card grey lighten-4">
            <div class="card-content">
                <form action="{{ route('thread.update', $thread) }}" method="post">
                    {{ csrf_field() }}
                    {{ method_field('PUT') }}
                    <div class="input-field">
                        <input
                            name="title"
                            type="text"
                            placeholder="{{ __('Thread Title')}}"
                            value="{{ $thread->title }}"
                        />
                    </div>
                    <div class="input-field">
                        <textarea
                            name="body"
                            class="materialize-textarea"
                            placeholder="{{ __('Thread Body')}}"
                        >{{ $thread->body }}</textarea>
                    </div>
                    <button type="submit" class="btn red accent-2">
                        {{ __('Send') }}
                    </button>
                    <a href="{{ route('thread.show', $thread) }}">{{ __('Back') }}</a>
                </form>
            </div>
        </div>
    </div>
@endsection
