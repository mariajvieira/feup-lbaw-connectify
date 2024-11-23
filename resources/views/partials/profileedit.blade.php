@extends('layouts.app')

@section('content')
<div class="profile-container">
    <h2>Editar Perfil de @ {{ $user->username }}</h2>

    <form action="{{ route('user.edit', ['id' => $user->id]) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" value="{{ old('username', $user->username) }}" class="form-control" />
        </div>

        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="form-control" />
        </div>

        <div class="form-group">
            <label for="user_password">Senha:</label>
            <input type="password" name="user_password" id="user_password" class="form-control" />
        </div>

        <div class="form-group">
            <label for="password_confirmation">Confirmar Senha:</label>
            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" />
        </div>

        <div class="form-group">
            <label for="profilePicture">Foto de Perfil:</label>
            <input type="file" name="profilePicture" id="profilePicture" class="form-control" />
        </div>

        <div class="form-group">
            <label for="is_public">Public Profile:</label>
            <input type="checkbox" name="is_public" id="is_public" value="1" {{ $user->is_public ? 'checked' : '' }} />
        </div>

        <button type="submit" class="btn btn-primary">Update Profile</button>
    </form>
</div>
@endsection
