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
