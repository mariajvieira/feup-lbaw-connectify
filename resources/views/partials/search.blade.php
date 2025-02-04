@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2 class="text-center mb-4">Search Results for: "{{ $query }}"</h2>

    <div class="row">


    <div class="row">
        <div class="col-12">
            <!-- Tabs -->
            <ul class="nav nav-tabs mb-4" id="searchTabs" role="tablist">
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
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ request('tab') === 'groups' ? 'active' : '' }}" 
                    id="groups-tab" 
                    href="?query={{ $query }}&tab=groups" 
                    role="tab">
                        Groups
                    </a>
                </li>
            </ul>

            <!-- Conteúdo das Tabs -->
            <div class="tab-content" id="searchTabsContent">
                @if(request('tab') === 'users' || !request('tab'))
                    <!-- Users Tab -->
                    <div class="tab-pane fade show active" id="users" role="tabpanel" aria-labelledby="users-tab">
                        <h3 class="h5">Users</h3>
                        @if($usersFullText->isEmpty())
                            <p>No users found.</p>
                        @else
                            <ul class="list-group">
                                @foreach ($usersFullText as $user)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <a class="text-decoration-none text-custom" 
                                            href="@if(auth()->check()) {{ route('user', $user->id) }} 
                                            @else {{ route('login') }} 
                                            @endif" 
                                            @if(!auth()->check()) 
                                                onclick="alert('You need to login to view profiles.'); window.location.href='{{ route('login') }}'; return false;"               
                                            @endif>
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
                        <h3 class="h5">Posts</h3>
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
                    <div class="tab-pane fade {{ request('tab') === 'comments' ? 'show active' : '' }}" id="comments" role="tabpanel" aria-labelledby="comments-tab">
                    <h3 class="h5">Comments</h3>
                    @if($commentsFullText->isEmpty())
                        <p>No comments found.</p>
                    @else
                        <ul class="list-group">
                            @foreach ($commentsFullText as $comment)
                                <li class="list-group-item ">
                                    <p>
                                        <strong class="text-decoration-none text-custom" >
                                            @if($comment->user)
                                                <a class="text-decoration-none text-custom" href="{{ route('user', ['id' => $comment->user->id]) }}">
                                                    {{ $comment->user->username }}
                                                </a>
                                            @else
                                                <span class="text-decoration-none text-custom" >User not found</span>
                                            @endif
                                        </strong>: 
                                        {{ $comment->comment_content }}
                                    </p>
                                    <p>
                                        <small>
                                            @if($comment->post)
                                                On post: 
                                                <a class="text-decoration-none text-custom fw-bold"  href="{{ route('post.reactions', [$comment->post_id]) }}">
                                                    {{ $comment->post->content }}
                                                </a>
                                            @else
                                                <span>Post not found</span>
                                            @endif
                                        </small>
                                    </p>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
                @elseif(request('tab') === 'groups')
    <!-- Groups Tab -->
    <div class="tab-pane fade {{ request('tab') === 'groups' ? 'show active' : '' }}" id="groups" role="tabpanel" aria-labelledby="groups-tab">
        <h3 class="h5">Groups</h3>
        @if($groupsFullText->isEmpty())
            <p>No groups found.</p>
        @else
            <ul class="list-group">
                @foreach ($groupsFullText as $group)
                    <li class="list-group-item">
                            <!-- Nome do Grupo e Link -->
                            <a href="{{ route('group.show', $group->id) }}" class="text-decoration-none text-custom fw-bold">
                                {{ $group->group_name }}
                            </a>
                            <!-- Descrição opcional -->
                            @if($group->description)
                                <p class="mb-0 text-muted">{{ $group->description }}</p>
                            @endif
                            

                    </li>
                @endforeach
            </ul>
        @endif
    </div>
@endif

                    
        


            </div>
        </div>
    </div>
</div>
@endsection
