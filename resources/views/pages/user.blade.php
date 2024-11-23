@extends('layouts.app')

@section('content')
<div class="profile-container">
    <h2>@ {{ $user->username }}</h2>

    <div class="profile-info">
        <p>{{ $user->email }}</p>
        <p>{{ $user->is_public ? 'Public' : 'Private' }}<strong> profile</strong> </p>
    </div>

    <h3>Posts</h3>
    <div class="user-posts">
        @include('pages.posts', ['posts' => $posts]) <!-- Chama a página de posts -->
    </div>

    <div class="edit-profile">
        <a href="{{ route('user.edit', $user->id) }}" class="btn btn-primary">Edit Profile</a>
    </div>
</div>
@endsection
