@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <!-- Profile Section -->
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div>
                <div class="card-body text-center">
                    <img src="{{ asset($user->profile_picture) }}" alt="Profile Picture" class="rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover; border: 2px solid #ddd;">
                    <h2 class="card-title">@ {{ $user->username }}</h2>

                    <div class="mb-3">
                        @if($user->id == Auth::id())
                            <p class="text-muted">{{ $user->email }}</p>
                        @endif
                        <p class="badge {{ $user->is_public ? 'bg-success' : 'bg-secondary' }}">
                            {{ $user->is_public ? 'Public Profile' : 'Private Profile' }}
                        </p>
                        @if($user->isAdmin())
                            <p class="text-danger fw-bold">Administrator</p>
                        @endif
                    </div>

                    <!-- Friendship Request -->
                    @if($user->id !== Auth::id())
                        @if(!$user->isFriend(Auth::user()))
                            @if(!$user->hasPendingRequestFrom(Auth::user()))
                                <form method="POST" action="{{ route('friend-request.send') }}" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="receiver_id" value="{{ $user->id }}">
                                    <button type="submit" class="btn btn-primary">Request Friendship</button>
                                </form>
                            @else
                                <p class="text-warning">Friendship request pending...</p>
                            @endif
                        @else
                            <p class="text-success">You are already friends!</p>
                        @endif
                    @endif

                    <!-- Show Friends Button -->
                    @if($user->id == Auth::id())
                        <a href="{{ route('user.friendsPage', ['id' => auth()->id()]) }}" class="btn btn-custom mt-3">
                        {{ $user->friends->count() }} Friends
                        </a>
                    @endif

                    <!-- Edit Profile -->
                    @can('editProfile', $user)
                        <a href="{{ route('user.edit', ['id' => $user->id]) }}" class="btn btn-custom mt-3">Edit Profile</a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Requests -->
    @if($user->id == Auth::id() && !$user->is_public)
        <div class="row mt-4">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5>Pending Requests</h5>
                    </div>
                    <div class="card-body">
                        @if($user->pendingRequests->isEmpty())
                            <p class="text-muted">No pending requests.</p>
                        @else
                            <ul class="list-group">
                                @foreach($user->pendingRequests as $request)
                                    <li class="list-group-item d-flex justify-content-between align-items-center" id="request-{{ $request->id }}">
                                        <span>{{ $request->sender->username }}</span>
                                        <div>
                                            <form method="POST" action="{{ route('friend-request.accept', ['id' => $request->id]) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">Accept</button>
                                            </form>
                                            <form method="POST" action="{{ route('friend-request.decline', ['id' => $request->id]) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                                            </form>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
    
    <h3>Friends</h3>
    <ul id="friends-list">
        <!-- A lista será preenchida dinamicamente via JavaScript -->
    </ul>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const userId = {{ auth()->id() }}; // Obtém o ID do usuário autenticado
    const friendsList = document.getElementById('friends-list');

    // Função para buscar amigos do usuário
    function fetchFriends() {
        fetch(`/user/${userId}/friends`)
            .then(response => {
                if (!response.ok) throw new Error('Erro ao carregar a lista de amigos.');
                return response.json();
            })
            .then(friends => {
                
                friendsList.innerHTML = '';

                if (friends.length === 0) {
                    friendsList.innerHTML = '<p>You have no friends yet</p>';
                    return;
                }
                console.log(friends)
                // Adiciona cada amigo na lista
                friends.forEach(friend => {
                    const listItem = document.createElement('li');
                    listItem.id = `friend-${friend.id}`;
                    listItem.innerHTML = `
                        <span>${friend.username}</span>
                        <button class="btn btn-danger btn-sm remove-btn" data-id="${friend.id}">
                            Remove
                        </button>
                    `;
                    friendsList.appendChild(listItem);
                });

                // Adiciona evento aos botões de remover
                document.querySelectorAll('.remove-btn').forEach(button => {
                    button.addEventListener('click', function () {
                        const friendId = this.dataset.id;
                        
                        removeFriend(friendId);
                    });
                });
            })
            .catch(error => console.error('Erro:', error));
    }

    // Função para remover um amigo
    function removeFriend(friendId) {
        fetch(`/friendship/remove/${friendId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                receiver_id: friendId 
            })
        })
            .then(response => {
                if (!response.ok) throw new Error('Erro ao remover amigo.', response.statusText);
                return response.json();
            })
            .then(data => {
                console.log(data.message);
                const friendItem = document.getElementById(`friend-${friendId}`);
                if (friendItem) friendItem.remove();
            })
            .catch(error => console.error('Erro:', error));
    }

    // Carrega a lista de amigos ao carregar a página
    fetchFriends();
});
</script>

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
