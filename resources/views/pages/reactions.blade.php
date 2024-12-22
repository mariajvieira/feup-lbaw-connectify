@extends('layouts.app')


@section('content')
<div class="container">
    @include('partials.post', ['post' => $post]) 

    <h4>Reactions</h4>

    @if ($reactions->isEmpty())
        <p>No reactions yet.</p>
    @else
        <div class="reaction-list">
        @foreach ($reactions as $reaction)
            <div class="reaction-item d-flex align-items-center mb-3">
                <i class="fa {{ $reaction->icon }} mr-2"></i>
                <strong>{{ $reaction->user->username }}</strong> : reacted with&nbsp;
                <span class="reaction-type">{{ ucfirst($reaction->reaction_type) }}</span>
            </div>
        @endforeach
        </div>
    @endif

    <a href="{{ route('home') }}" class="btn btn-primary mt-3">Back to Posts</a>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif


@endsection
