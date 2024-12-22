@extends('layouts.app')


@section('content')
<div class="group-details-container">
    <h1>{{ $group->group_name }}</h1>
    <p><strong>Description:</strong> {{ $group->description }}</p>
    <p><strong>Public:</strong> {{ $group->is_public ? 'Yes' : 'No' }}</p>

    <!-- Verifica se o grupo é público e se o usuário não é o dono do grupo -->
    @if($group->is_public && !$group->users->contains(Auth::user()->id))
        <button id="join-group" data-group-id="{{ $group->id }}" class="btn btn-primary">Join this Public Group</button>
    @elseif($group->users->contains(Auth::user()->id))
        <p>You are a member of this group!</p>
    @endif

    <h3>Group Members:</h3>
    <ul>
        <li><strong>Owner:</strong> {{ $group->owner->name }} ({{ $group->owner->email }})</li>
        @foreach($members as $member)
            <li>{{ $member->name }} ({{ $member->email }})</li>
        @endforeach
        <!-- Exibir o proprietário corretamente -->
    </ul>
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
