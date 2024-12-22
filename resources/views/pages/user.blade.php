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




<div class="container mt-5">
    <!-- Profile Section -->
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div>
                <div class="card-body text-center">
                    <img src="{{ route('profile.picture', parameters: ['id' => $user->id]) }}" alt="Profile Picture" class="rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover; border: 2px solid #ddd;">

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

                    @if($user->id !== Auth::id())
                        @if($user->is_public)
                            @if(!$user->isFriend(Auth::user()))
                                <form method="POST" action="{{ route('friend-request.send') }}" class="d-inline"> 
                                    @csrf
                                    <input type="hidden" name="receiver_id" value="{{ $user->id }}">
                                    <button type="submit" class="btn btn-primary">Follow</button> 
                                </form>
                            @else
                                <p class="text-success">You are already following this user!</p>
                            @endif
                        @else
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
                    @endif

                    @if(Auth::check())
                    <a href="{{ route('user.friendsPage', ['id' => $user->id]) }}" class="btn btn-custom">
                        {{ $user->friends->count() }} Friends
                    </a>
                    @endif

                    @can('editProfile', $user)
                        <a href="{{ route('user.edit', ['id' => $user->id]) }}" class="btn btn-custom mt-3"><i class="fa-solid fa-pen"></i></a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Requests -->
    @if($user->id == Auth::id() && !$user->is_public)
        <div class="row mt-4">
            <div class="col-md-8 offset-md-2">
                    <h5 class="mb-3">Friend Requests</h5>
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
    @endif



    <div class="row mt-4">
        <div class="col-md-8 offset-md-2">
            <h3 class="mb-3">Groups:</h3>
            @if($user->groups->isEmpty())
                <p>You don't belong to any group yet.</p>
            @else
                <ul class="list-group">
                    @foreach($user->groups as $group)
                        <li class="list-group-item">
                            <strong>{{ $group->group_name }}</strong><br>
                            <small>{{ $group->description ?? 'Sem descrição' }}</small>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
        <div class="col-md-8 offset-md-2">
            <h3 class="mb-3">Groups you own:</h3>
            @if($user->ownedGroups->isEmpty())
                <p>You don't  own any group yet.</p>
            @else
                <ul class="list-group">
                    @foreach($user->ownedGroups as $group)
                        <li class="list-group-item">
                            <strong>{{ $group->group_name }}</strong><br>
                            <small>{{ $group->description ?? 'Sem descrição' }}</small>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <!-- Posts Section -->
    <div class="row mt-4">
        <div class="col-md-8 offset-md-2">
            <h5 class="mb-3">Posts</h5>

            @can('seePosts', $user)
                @if($posts->isNotEmpty())
                    @foreach($posts as $post)
                        <div class="mb-4">
                            @include('partials.post', ['post' => $post]) 
                        </div>
                    @endforeach
                @else
                    <p class="text-muted">No posts available.</p>
                @endif
            @else
                <p class="text-muted">You do not have permission to see this user's posts.</p>
            @endcan
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger mt-3">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.accept-request-form, .reject-request-form').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            const url = this.action;
            const requestId = this.closest('.list-group-item').id;

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
                        alert('Failed to process the request.');
                    }
                })
                .catch(error => console.error('Error:', error));
        });
    });
});
</script>
@endsection
