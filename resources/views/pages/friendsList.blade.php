@extends('layouts.app')

@section('content')
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
            <h2 class="text-center mb-4">Friends</h2>

            <!-- Caso o usuário não tenha amigos -->
            @if($friends->isEmpty())
                <p class="text-center">No friends yet.</p>
            @else
                <ul id="friends-list" class="list-group" data-user-id="{{ $user->id }}">
                    @foreach($friends as $friend)
                        <li class="list-group-item d-flex justify-content-between align-items-center" id="friend-{{ $friend->id }}">
                            <a href="{{ route('user', ['id' => $friend->id]) }}" class="text-decoration-none text-custom">
                                <span>{{ $friend->username }}</span>
                            </a>
                            <form action="{{ route('friendship.remove', ['id' => $friend->id]) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-sm">
                                    Remove
                                </button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>


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
