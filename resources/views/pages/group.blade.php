@extends('layouts.app')

@section('content')
<div class="group-details-container">
    <h1>{{ $group->group_name }}</h1>
    <p><strong>Description:</strong> {{ $group->description }}</p>
    <p><strong>Public:</strong> {{ $group->is_public ? 'Yes' : 'No' }}</p>

    <!-- Botões para o grupo -->
    @if($group->is_public && !$group->users->contains(Auth::user()->id))
        <button id="join-group" data-group-id="{{ $group->id }}" class="btn btn-primary">Join this Public Group</button>
    @elseif($group->users->contains(Auth::user()->id))
        <p>You are a member of this group!</p>
        <a href="{{ route('group.leave', $group->id) }}" class="btn btn-danger">Leave Group</a>
    @endif

    <!-- Botão para visualizar os membros -->
    @if($group->users->contains(Auth::user()->id))
        <a href="{{ route('group.members', $group->id) }}" class="btn btn-secondary">View Members</a>
    @endif

    <hr>

    <!-- Lista de posts do grupo -->
    <h2>Posts in {{ $group->group_name }}</h2>
    @if($posts->isEmpty())
        <p>No posts in this group yet.</p>
    @else
        <ul class="group-posts-list">
            @foreach($posts as $post)
                <li>
                    <h4>{{ $post->title }}</h4>
                    <p>{{ $post->content }}</p>
                    <small>Posted by: {{ $post->user->username }} on {{ $post->created_at->format('d M Y') }}</small>
                </li>
                <hr>
            @endforeach
        </ul>
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
