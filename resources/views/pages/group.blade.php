@extends('layouts.app')

@section('content')


<div class="group-details-container">
    <h1>{{ $group->group_name }}</h1>
    <p><strong>Description:</strong> {{ $group->description }}</p>
    <p><strong>Public:</strong> {{ $group->is_public ? 'Yes' : 'No' }}</p>

    <!-- Botões para o grupo -->
    @if($group->is_public && !$group->users->contains(Auth::user()->id) && $group->owner_id !== Auth::user()->id)
        <button id="join-group" data-group-id="{{ $group->id }}" class="btn btn-custom">Join this Public Group</button>
    @elseif($group->users->contains(Auth::user()->id) && $group->owner_id !== Auth::user()->id)
        <p>You are a member of this group!</p>
        <a href="{{ route('group.leave', $group->id) }}" class="btn btn-danger">Leave Group</a>
    @endif


    
    @if ($group->is_public == false && !$group->users->contains(Auth::user()->id) && $group->owner_id !== Auth::user()->id)
        @if ($group->pendingJoinRequests->contains('user_id', Auth::user()->id))
            <p>Your request to join this group is pending.</p>
        @else
        <form action="{{ route('join-group') }}" method="POST">
            @csrf
            <input type="hidden" name="group_id" value="{{ $group->id }}">
            <button class="btn btn-custom" type="submit">Send join request</button>
        </form>
        @endif
    @endif

    @if ($group->owner_id == auth()->id())
        <!-- Exibe o botão para acessar os pedidos de adesão se o utilizador for o owner -->
        <a href="{{ route('manage-requests', $group->id) }}">
            <button class="btn btn-custom">Manage join requests</button>
        </a>
    @endif

    <!-- Botão de Postagem (apenas para membros ou donos) -->
    @if($group->users->contains(Auth::user()->id) || $group->owner_id === Auth::user()->id)
        <a href="{{ route('group.post.create', $group->id) }}" class="btn btn-success">Post something</a>
    @endif

    <!-- Botão para visualizar os membros -->
    @if($group->users->contains(Auth::user()->id) || $group->owner_id === Auth::user()->id)
        <a href="{{ route('group.members', $group->id) }}" class="btn btn-secondary">View Members</a>
    @endif

    <hr>

    <!-- Lista de posts do grupo -->
    <h2>Posts in {{ $group->group_name }}</h2>
    @if($group->is_public || $group->users->contains(Auth::user()->id) || $group->owner_id === Auth::user()->id)
        @if($posts->isEmpty())
            <p>No posts in this group yet.</p>
        @else
            <ul class="group-posts-list">
                @include('pages.posts', ['posts' => $posts])            
            </ul>
        @endif
    @else
        <p>You need to join this group to see its posts.</p>
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
