@extends('layouts.app')

@section('content')
<div class="profile-container">
    <h2>@ {{ $user->username }}</h2>

    <!-- Formulário para editar o perfil (username, email, foto de perfil, visibilidade) -->
    <form action="{{ route('user.update', ['id' => $user->id]) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <h3>Edit Profile</h3>

        <!-- Editar Username -->
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" value="{{ old('username', $user->username) }}" class="form-control" />
        </div>

        <!-- Editar Email -->
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="form-control" />
        </div>


        <!-- Visibilidade do Perfil -->
        <div class="form-group">
            <label for="is_public">Public Profile:</label>
            <input type="checkbox" name="is_public" id="is_public" value="1" {{ $user->is_public ? 'checked' : '' }} />
        </div>

        <!-- Botão para atualizar perfil -->
        <button type="submit" class="btn btn-primary">Update Profile</button>
    </form>

    <!-- Formulário para alterar senha -->
    <form action="" method="POST">
        @csrf
        @method('PUT')

        <h3>Change Password</h3>

        <!-- Senha Antiga -->
        <div class="form-group">
            <label for="old_password">Password:</label>
            <input type="password" name="old_password" id="old_password" class="form-control" required />
        </div>

        <!-- Nova Senha -->
        <div class="form-group">
            <label for="new_password">New Password:</label>
            <input type="password" name="new_password" id="new_password" class="form-control" required />
        </div>

        <!-- Confirmar Nova Senha -->
        <div class="form-group">
            <label for="new_password_confirmation">Confirm New Password:</label>
            <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="form-control" required />
        </div>

        <!-- Botão para alterar a senha -->
        <button type="submit" class="btn btn-primary">Update Password</button>
    </form>
</div>
@endsection
