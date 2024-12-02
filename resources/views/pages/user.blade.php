@extends('layouts.app')

@section('content')
<div class="profile-container">
    <h2>@ {{ $user->username }}</h2>

    <div class="profile-info">
        @if($user->id==Auth::id())
            <p>{{ $user->email }}</p>
        @endif
        <p>{{ $user->is_public ? 'Public' : 'Private' }} <strong>profile</strong></p>
        @if($user->isAdmin()) <!-- Verifica se o usuário é administrador -->
            <p><strong>Administrator</strong></p> <!-- Exibe "Administrator" -->
        @endif
    </div>

    <div class="friendShip-request">
        @if($user->id !== Auth::id()) 
            @if(!$user->isFriend(Auth::user())) 
                @if(!$user->hasPendingRequestFrom(Auth::user())) 
                    <form method="POST" action="{{ route('friend-request.send') }}">
                        @csrf
                        <input type="hidden" name="receiver_id" value="{{ $user->id }}">
                        <button type="submit" class="btn btn-primary">Request Friendship</button>
                    </form>
                @else
                    <p class="text-muted">Friendship request pending...</p>
                @endif
            @else
                <p class="text-success">You are already friends!</p>
            @endif
        @endif
    </div>

    <div class="List Friends">
    @if($user->id == Auth::id())
        <button id="toggleFriends" class="btn btn-primary">Mostrar Amigos</button>
        <ul id="friendsList" style="display: none; margin-top: 10px;"></ul>
    @endif
    </div>





    <div class="edit-profile">
        @can('editProfile', $user)
            <a href="{{ route('user.edit', ['id' => $user->id]) }}" class="btn btn-primary">Edit Profile</a>
        @endcan
    </div>

    <div class="pending-request">
        @if(($user->id == Auth::id())&&($user->is_public==false))
            <h3>Pending Requests</h3>
            @if($user->pendingRequests()->count() > 0)
                @foreach($user->pendingRequests() as $request)
                    <div class="pending-request-item">
                        <p>{{ $request->sender->username }}</p>
                        <form method="POST" action="{{ route('friend-request.accept') }}">
                            @csrf
                            <input type="hidden" name="sender_id" value="{{ $request->sender->id }}">
                            <button type="submit" class="btn btn-primary">Accept</button>
                        </form>
                        <form method="POST" action="{{ route('friend-request.reject') }}">
                            @csrf
                            <input type="hidden" name="sender_id" value="{{ $request->sender->id }}">
                            <button type="submit" class="btn btn-danger">Reject</button>
                        </form>
                    </div>
                @endforeach
            @endif
        @endif
    </div>
               


    <h3>Posts</h3>
    <div class="user-posts">
        @include('pages.posts', ['posts' => $posts]) 
    </div>
</div>
@endsection

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const toggleButton = document.getElementById("toggleFriends");
        const friendsList = document.getElementById("friendsList");

        toggleButton.addEventListener("click", function () {
            if (friendsList.style.display === "none") {
                fetch(`{{ route('user.friends', $user->id) }}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            alert(data.error);
                            return;
                        }

                        friendsList.innerHTML = "";

                        data.forEach(friend => {
                            const li = document.createElement("li");
                            li.textContent = friend.username;
                            friendsList.appendChild(li);
                        });

                        friendsList.style.display = "block";
                        toggleButton.textContent = "Ocultar Amigos";
                    })
                    .catch(error => console.error('Erro ao carregar amigos:', error));
            } else {
                friendsList.style.display = "none";
                toggleButton.textContent = "Mostrar Amigos";
            }
        });
    });
</script>
