@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Search Results for: "{{ $query }}"</h2>

    <div class="filters-column">
        <h3>Filters</h3>
        <form id="filter-form">
            <label for="filter-date">Date:</label>
            <input type="date" id="filter-date" name="filter-date" />
            
            <button type="submit" class="apply-filters-btn">Apply Filters</button>
        </form>
    </div>
    <div class="results">
    <!-- Tabs -->
        <ul class="nav nav-tabs" id="searchTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link {{ request('tab') === 'users' || !request('tab') ? 'active' : '' }}" 
                id="users-tab" 
                href="?query={{ $query }}&tab=users" 
                role="tab">
                    Users
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link {{ request('tab') === 'posts' ? 'active' : '' }}" 
                id="posts-tab" 
                href="?query={{ $query }}&tab=posts" 
                role="tab">
                    Posts
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link {{ request('tab') === 'comments' ? 'active' : '' }}" 
                id="comments-tab" 
                href="?query={{ $query }}&tab=comments" 
                role="tab">
                    Comments
                </a>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content mt-4" id="searchTabsContent">
            @if(request('tab') === 'users' || !request('tab'))
                <!-- Users Tab -->
                <div class="tab-pane fade show active" id="users" role="tabpanel" aria-labelledby="users-tab">
                    <h3>Users</h3>
                    @if($usersFullText->isEmpty())
                        <p>No users found.</p>
                    @else
                        <ul class="user-list">
                            @foreach ($usersFullText as $user)
                                <li>
                                    <a 
                                        href="@if(auth()->check()) {{ route('user', $user->id) }} @else {{ route('login') }} @endif"
                                        @if(!auth()->check()) 
                                            onclick="alert('You need to login view profiles.'); window.location.href='{{ route('login') }}'; return false;"               
                                        @endif
                                    >
                                        @ {{ $user->username }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

            @elseif(request('tab') === 'posts')
                <!-- Posts Tab -->
                <div class="tab-pane fade show active" id="posts" role="tabpanel" aria-labelledby="posts-tab">
                    <h3>Posts</h3>
                    @if($postsFullText->isEmpty())
                        <p>No posts found.</p>
                    @else
                        <div class="post-list">
                            @include('pages.posts', ['posts' => $postsFullText])
                        </div>
                    @endif
                </div>
            @elseif(request('tab') === 'comments')
                <!-- Comments Tab -->
                <div class="tab-pane fade" id="comments" role="tabpanel" aria-labelledby="comments-tab">
                    <h3>Comments</h3>
                    @if($commentsFullText->isEmpty())
                        <p>No comments found.</p>
                    @else
                        <ul class="comment-list">
                            @foreach ($commentsFullText as $comment)
                                <li>
                                    <p>
                                        <strong>
                                            <a href="{{ route('user', ['id' => $comment->user->id]) }}">
                                                {{ $comment->user->username }}
                                            </a>
                                        </strong>: 
                                        {{ $comment->comment_content }}
                                    </p>
                                    <p>
                                        <small>
                                            On post: <a href="{{ route('post', ['id' => $comment->post_id]) }}">{{ $comment->post->content }}</a>
                                        </small>
                                    </p>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

            @endif
        </div>
    </div>
</div>
@endsection
