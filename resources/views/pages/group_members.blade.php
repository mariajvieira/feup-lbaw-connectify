@extends('layouts.app')

@section('content')
<div class="group-members-container">
    <h1>{{ $group->group_name }} - Members</h1>

    <!-- Botão para voltar ao grupo -->
    <a href="{{ route('group.show', $group->id) }}" class="btn btn-primary">Back to Group</a>

    <h3>Owner:</h3>
    <p>{{ $group->owner->name }} {{ $group->owner->username }}</p>

    <h3>Other Members:</h3>
    <ul>
        @foreach($members as $member)
            @if($member->id !== $group->owner_id) <!-- Ignora o proprietário do grupo -->
                <li>
                    {{ $member->name }} {{ $member->username }}
                    
                    <!-- Se o utilizador autenticado for o proprietário, mostra o botão de remoção -->
                    @if(Auth::check() && Auth::user()->id === $group->owner_id)
                        <form action="{{ route('group.removeMember', ['group' => $group->id, 'user' => $member->id]) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to remove this member?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                        </form>
                    @endif
                </li>
            @endif
        @endforeach
    </ul>
</div>
@endsection
