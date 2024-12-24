@extends('layouts.app')







@section('content')
<!-- resources/views/join_group.blade.php -->

@if ($group->is_public == false)
    <!-- Exibe o formulário de adesão apenas para grupos privados -->
    <form action="{{ route('join-group') }}" method="POST">
        @csrf
        <input type="hidden" name="group_id" value="{{ $group->id }}">
        <button type="submit">Solicitar adesão</button>
    </form>
@else
    <!-- Caso o grupo seja público, você pode exibir outra mensagem ou ação -->
    <p>Este é um grupo público. Não é necessário solicitar adesão.</p>
@endif

<!-- resources/views/group.blade.php -->

@if ($group->owner_id == auth()->id())
    <!-- Exibe o botão para acessar os pedidos de adesão se o utilizador for o owner -->
    <a href="{{ route('manage-requests', $group->id) }}">
        <button>Gerir Pedidos de Adesão</button>
    </a>
@endif

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
            @include('pages.posts', ['posts' => $posts])            
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
