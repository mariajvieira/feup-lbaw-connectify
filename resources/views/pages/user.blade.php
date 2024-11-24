@extends('layouts.app')

@section('content')
<div class="profile-container">
    <h2>@ {{ $user->username }}</h2>

    <div class="profile-info">
        <p>{{ $user->email }}</p>
        <p>{{ $user->is_public ? 'Public' : 'Private' }}<strong> profile</strong> </p>
    </div>
    <div class="edit-profile">
        @if(auth()->id() === $user->id)
            <a href="{{ route('user.edit', ['id' => $user->id]) }}" class="btn btn-primary">Edit Profile</a>
        @endif
    </div>

    <h3>Posts</h3>
    <div class="user-posts">
        @include('pages.posts', ['posts' => $posts]) 
    </div>


</div>
@endsection
