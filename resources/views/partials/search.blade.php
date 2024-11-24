@extends('layouts.app')

@section('content')
<div class="search-results-container">
    <h2>Search Results for: "{{ $query }}"</h2>

    <h3>Users</h3>
    @if($usersFullText->isEmpty())
        <p>No users found matching your query.</p>
    @else
        <ul class="user-list">
            @foreach ($usersFullText as $user)
                <li>
                    <a href="{{ route('user', $user->id) }}">
                        @ {{ $user->username }}
                    </a>
                </li>
            @endforeach
        </ul>
    @endif

    <h3>Posts</h3>
    @if($postsFullText->isEmpty())
        <p>No posts found matching your query.</p>
    @else
        <ul class="post-list">
            @foreach ($postsFullText as $post)
                <li>
                    <a href="{{ route('post.show', $post->id) }}">
                        <p>{{ Str::limit($post->content, 100) }}</p>
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
</div>
@endsection