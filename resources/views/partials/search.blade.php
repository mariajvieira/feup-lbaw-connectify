@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Search Results for: "{{ $query }}"</h2>

    <h3>Users</h3>
    @if($usersFullText->isEmpty())
        <p>No users found.</p>
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
        <p>No posts found.</p>
    @else
        <div class="post-list">
            @include('pages.posts', ['posts' => $postsFullText])
        </div>
    @endif
</div>
@endsection
