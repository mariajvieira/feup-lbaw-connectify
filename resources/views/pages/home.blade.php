@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Feed de Posts</h1>

    @if($posts->isEmpty())
        <p>No posts to show.</p>
    @else
        <div class="post-list">
            @include('pages.posts', ['posts' => $posts]) 
        </div>
    @endif
</div>
@endsection
