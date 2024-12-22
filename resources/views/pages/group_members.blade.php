@extends('layouts.app')

@section('content')

<div class="group-members-container">

    <h1>{{ $group->group_name }} - Members</h1>

    <!-- Lista de membros -->
    <h3>Members:</h3>
    <ul>
        @foreach($members as $member)
            <li>
                {{ $member->username }}
                
                <!-- Verificar se o usuário é o proprietário e não tentar remover o proprietário -->
                @if(Auth::id() == $group->owner_id && $member->id != $group->owner_id)
                    <form action="{{ route('group.removeMember', [$group->id, $member->id]) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                    </form>
                @endif
            </li>
        @endforeach
    </ul>

    <!-- Adicionar amigos ao grupo (apenas para o owner) -->
    @if(Auth::id() == $group->owner_id)

    <h3>Add a Friend to Group</h3>

    @if($friends->isEmpty())
        <p>No friends available to add.</p>
    @else
        <form action="{{ route('group.addFriend', $group->id) }}" method="POST">
            @csrf
            <select name="friend_id" class="form-select">
                @foreach($friends as $friend)
                    <option value="{{ $friend->id }}">{{ $friend->username }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary mt-2">Add Friend</button>
        </form>
    @endif

    @endif

</div>

@endsection
