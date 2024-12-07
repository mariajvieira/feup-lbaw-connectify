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
        <button id="load-friends" data-user-id="{{ auth()->id() }}" >List friends</button>
        <ul id="friendsList" style="display: none; margin-top: 10px;"></ul>
    @endif
    </div>





    <div class="edit-profile">
        @can('editProfile', $user)
            <a href="{{ route('user.edit', ['id' => $user->id]) }}" class="btn btn-primary">Edit Profile</a>
        @endcan
    </div>

<div class="pending-request">
    @if($user->id == Auth::id() && !$user->is_public)
        <h3>Pending Requests</h3>
        @if($user->pendingRequests->isEmpty())
            <p>No pending requests.</p>
        @else
            <ul>
                @foreach($user->pendingRequests as $request)
                    <div class="pending-request-item" id="request-{{ $request->id }}">
                        <p>{{ $request->sender->username }}</p>
                        <form method="POST" action="{{ route('friend-request.accept', ['id' => $request->id]) }}" class="accept-request-form">
                            @csrf
                            <input type="hidden" name="sender_id" value="{{ $request->sender->id }}">
                            <button type="submit" class="btn btn-primary">Accept</button>
                        </form>
                        <form method="POST" action="{{ route('friend-request.decline', ['id' => $request->id]) }}" class="reject-request-form">
                            @csrf
                            <input type="hidden" name="sender_id" value="{{ $request->sender->id }}">
                            <button type="submit" class="btn btn-danger">Reject</button>
                        </form>
                    </div>
                @endforeach
            </ul>
        @endif
    @endif
</div>



               


    <h3>Posts</h3>
    <div class="user-posts">
        @can('seePosts', $user)
            @include('pages.posts', ['posts' => $posts]) 
        @else 
            <p>You have no permissions to see this user's posts. </p>
        @endcan
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('load-friends').addEventListener('click', function () {
    const userId = this.getAttribute('data-user-id');
    console.log(userId);
    const friendsListDiv = document.getElementById('friendsList');

    fetch(`/users/${userId}/friends`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to fetch friends');
            }
            return response.json();
        })
        .then(friends => {
            if (friends.length > 0) {
                friendsListDiv.style.display = 'block';
                friendsListDiv.innerHTML = friends.map(friend => 
                    `<p>${friend.username}</p>`
                ).join('');
            } else {
                friendsListDiv.style.display = 'block';
                friendsListDiv.innerHTML = '<p>No friends found.</p>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            friendsListDiv.style.display = 'block';
            friendsListDiv.innerHTML = '<p>Could not load friends.</p>';
        });
});
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.accept-request-form, .reject-request-form').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault(); 

            const formData = new FormData(this); 
            const url = this.action; 
            const requestId = this.closest('.pending-request-item').id; 

            fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById(requestId).remove(); 
                    } else {
                        alert('Falha ao processar a solicitação');
                    }
                })
                .catch(error => console.error('Erro:', error));
        });
    });
});

    </script>

@endsection