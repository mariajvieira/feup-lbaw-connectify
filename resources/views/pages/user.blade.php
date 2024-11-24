@extends('layouts.app')

@section('content')
<div class="profile-container">
    <h2>@ {{ $user->username }}</h2>

    <div class="profile-info">
        <p>{{ $user->email }}</p>
        <p>{{ $user->is_public ? 'Public' : 'Private' }}<strong> profile</strong> </p>
        @if($user->isAdmin()) <!-- Verifica se o usuário é administrador -->
            <p><strong>Administrator</strong></p> <!-- Exibe "Administrator" -->
        @endif
    </div>

    <div class="edit-profile">
        @can('editProfile', $user)
            <a href="{{ route('user.edit', ['id' => $user->id]) }}" class="btn btn-primary">Edit Profile</a>
        @endcan
    </div>

    <h3>Posts</h3>
    <div class="user-posts">
        @include('pages.posts', ['posts' => $posts]) 
    </div>
</div>
@endsection
