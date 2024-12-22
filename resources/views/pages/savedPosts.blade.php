@extends('layouts.app')



@section('content')
<div class="container">
    <h2>Saved Posts</h2>

    @if($posts->isEmpty())
        <p>No post saved yet.</p>
    @else
        <div class="post-list">
            @include('pages.posts', ['posts' => $posts])
        </div>
    @endif
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
