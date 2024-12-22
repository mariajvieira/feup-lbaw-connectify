@extends('layouts.app')



@section('content')

<!-- Botão para Excluir Conta -->
<button type="button" class="btn btn-danger" id="deleteAccountBtn">
    Apagar Conta
</button>

<!-- Modal de Confirmação -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmDeleteModalLabel">Tem certeza?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar" id="closeModalBtn">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Você tem certeza que deseja apagar a sua conta? Essa ação não pode ser desfeita.
            </div>
            <div class="modal-footer">
                <!-- Botão Cancelar -->
                <button type="button" class="btn btn-secondary" id="cancelBtn">Cancelar</button>
                <!-- Botão Confirmar -->
                <form action="{{ route('delete.account') }}" method="POST" id="deleteAccountForm">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Sim, apagar conta</button>
                </form>
            </div>
        </div>
    </div>
</div>




<div class="container">
    <div class="row">
        <!-- Coluna para os grupos -->
        <div class="col-md-3">
            @include('partials.group-list', ['groups' => $user->groups])
        </div>
<div class="profile-container">
<img src="{{ asset($user->profile_picture) }}" alt="Profile Picture" style="max-width: 150px; max-height: 150px; display: block; border: 1px solid #ddd; padding: 5px;">
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

    <div class="friendship-request">
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
        <a href="{{ route('user.friendsPage', ['id' => auth()->id()]) }}" class="btn btn-primary">
            Show Friends
        </a>
    @endif
</div>

<div class="edit-profile">
        @can('editProfile', $user)
            <a href="{{ route('user.edit', ['id' => $user->id]) }}" class="btn btn-primary">Edit Profile</a>
        @endcan
    </div>



<style>
    .container {
        display: flex;
        justify-content: space-between; /* Para garantir que as colunas tenham espaço entre si */
        margin-top: 20px;
    }
    .column {
        width: 45%; /* Define a largura das colunas */
        padding: 15px;
        box-sizing: border-box; /* Inclui padding e border dentro da largura */
    }
</style>



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