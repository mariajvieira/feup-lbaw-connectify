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
            @foreach ($posts as $post)
                <div class="card mb-4 shadow-sm border-primary" style="border-width: 2px; border-radius: 8px;">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">{{ $post->user->username }}</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">{{ $post->content }}</p>
                        
                        @if ($post->image)
                            <div class="mb-3">
                                <img src="{{ asset('storage/' . $post->image) }}" alt="Post Image" class="img-fluid rounded">
                            </div>
                        @endif
                        
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <small class="text-muted">Posted on {{ $post->post_date->format('F j, Y, g:i a') }}</small>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
