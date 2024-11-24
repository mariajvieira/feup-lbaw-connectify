@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="display-4 text-center mb-4">Welcome to Our Community!</h1>
    <p class="lead text-center mb-5">Browse public posts from our community and share your thoughts!</p>

    @if($posts->isEmpty())
        <div class="alert alert-info text-center">
            <strong>No public posts available.</strong> Be the first to share!
        </div>
    @else
        <div class="post-list">
            @include('pages.posts', ['posts' => $posts]) 
        </div>
    @endif
</div>
@endsection
