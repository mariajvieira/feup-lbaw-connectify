@extends('layouts.app')

@section('content')
<div class="container">
    <div class="post-item">
        <div class="post-header d-flex justify-content-between align-items-center">
            <div class="user-info">
                <h5>
                    <strong>
                        <a href="{{ route('user', ['id' => $post->user->id]) }}">
                            @ {{ $post->user->username }}
                        </a>
                    </strong>
                </h5>
            </div>
            @can('edit', $post)
            <!-- Botões Editar e Deletar lado a lado -->
            <div class="post-actions d-flex align-items-center gap-2">
                <a class="btn btn-primary" href="{{ route('post.edit', $post->id) }}"><i class="fa-solid fa-pen"></i></a>
                <form action="{{ route('post.delete', $post->id) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger d-flex align-items-center gap-2" onclick="return confirm('Are you sure you want to delete this post?')">
                        <i class="fa-solid fa-trash"></i>
                    </button>            
                </form>
            </div>
            @endcan
        </div>

        <p class="post-content">{{ $post->content }}</p>

<!-- Renderizar imagens dinamicamente -->
        <div class="post-images mt-3">
            @foreach (['image1', 'image2', 'image3'] as $imageField)
                @if (!empty($post->$imageField))
                    <div class="post-image mb-2">
                        <img src="{{ asset($post->$imageField) }}" class="img-fluid" alt="Post Image">
                    </div>
                @endif
            @endforeach
        </div>
        
        <span class="post-date">Published at: {{ \Carbon\Carbon::parse($post->post_date)->format('d/m/Y \a\t H:i') }}</span>
    </div>

    <div class="reactions mt-3">
        @foreach ([
            'like' => 'fa-regular fa-heart',
            'laugh' => 'fa-regular fa-face-laugh-squint',
            'cry' => 'fa-regular fa-face-sad-cry',
            'applause' => 'fa-solid fa-hands-clapping', 
            'shocked' => 'fa-regular fa-face-surprise'
            ] as $reaction => $icon)
            <button 
                class="reaction-button {{ $userReaction && $userReaction->reaction_type === $reaction ? 'selected' : '' }}" 
                data-reaction-type="{{ $reaction }}" 
                data-post-id="{{ $post->id }}"
                @if (!auth()->check())
                    onclick="alert('You need to login to react.'); window.location.href='{{ route('login') }}'; return false;"                
                @else
                    data-reaction-id="{{ $userReaction && $userReaction->reaction_type === $reaction ? $userReaction->id : '' }}"
                @endif
            >
                <i class="{{ $icon }}"></i> 
            </button>
        @endforeach
    </div>

    <hr>

    <h4>Reactions</h4>

    @if ($reactions->isEmpty())
        <p>No reactions yet.</p>
    @else
        <div class="reaction-list">
        @foreach ($reactions as $reaction)
            <div class="reaction-item d-flex align-items-center mb-3">
                <i class="fa {{ $reaction->icon }} mr-2"></i>
                <strong>{{ $reaction->user->username }}</strong> reacted with
                <span class="reaction-type">{{ ucfirst($reaction->reaction_type) }}</span>
            </div>
        @endforeach

        </div>
    @endif

    <a href="{{ route('home') }}" class="btn btn-primary mt-3">Back to Posts</a>
</div>
@endsection

