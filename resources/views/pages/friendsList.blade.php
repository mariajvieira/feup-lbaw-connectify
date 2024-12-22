@extends('layouts.app')



@section('content')
<div class="container">
    <h3>My Friends</h3>
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
                    friendsList.innerHTML = '<p>You have no friends yet/p>';
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
