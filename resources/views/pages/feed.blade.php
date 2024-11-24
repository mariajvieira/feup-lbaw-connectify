@extends('layouts.app')  <!-- Incluindo o layout base -->

@section('content')
    <div class="feed-container">
        @foreach($posts as $post)
            <div class="post-item">
                <div class="post-header">
                    <div class="user-info">
                        <h5>{{ $post->user->username }}</h5>
                        <a href="{{ route('user', ['id' => $post->user->id]) }}">@ {{ $post->user->username }}</a>
                    </div>
                    <span class="post-date">{{ $post->post_date->diffForHumans() }}</span>
                </div>
                <div class="post-content">
                    <p>{{ $post->content }}</p>
                </div>
                @if($post->image)
                    <div class="post-image">
                        <img src="{{ asset('images/'.$post->image) }}" alt="Post Image">
                    </div>
                @endif
                <div class="post-actions">
                    <a href="#" class="btn btn-primary">Curtir</a>
                    <a href="#" class="btn btn-danger">Comentar</a>
                    <a href="#" class="btn btn-primary">Compartilhar</a>
                </div>
            </div>
        @endforeach
    </div>
@endsection
