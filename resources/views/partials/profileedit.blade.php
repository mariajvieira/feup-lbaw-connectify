@extends('layouts.app')

@section('content')
<div class="container profile-container mt-5">
    <h2 class="text-center mb-4">@ {{ $user->username }}</h2>

    <!-- Formulário para editar o perfil (username, email, foto de perfil, visibilidade) -->
    <form action="{{ route('user.update', ['id' => $user->id]) }}" method="POST" enctype="multipart/form-data" class="border p-4 rounded shadow-sm">
        @csrf
        @method('PUT')

        <h3>Edit Profile</h3>
        
        <!-- Exibição da imagem de perfil atual -->
        <div class="form-group mb-3">
            <label for="profile_picture" class="form-label">Profile Picture:</label>
            @if (!empty($user->profile_picture))
                <div style="margin-bottom: 10px;">
                    <img src="{{ asset($user->profile_picture) }}" alt="Profile Picture" class="rounded-circle mb-4" style="width: 150px; height: 150px; object-fit: cover; border: 2px solid #ddd;">
                </div>
            @endif
            <input type="file" name="profile_picture" id="profile_picture" class="form-control" />
        </div>

        <!-- Editar Username -->
        <div class="form-group mb-3">
            <label for="username" class="form-label">Username:</label>
            <input type="text" name="username" id="username" value="{{ old('username', $user->username) }}" class="form-control" />
        </div>

        <!-- Editar Email -->
        <div class="form-group mb-3">
            <label for="email" class="form-label">Email:</label>
            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="form-control" />
        </div>

        <!-- Visibilidade do Perfil -->
        <div class="form-group mb-3">
            <label for="is_public" class="form-label">Public Profile:</label>
            <div class="form-check">
                <input type="hidden" name="is_public" value="0">
                <input type="checkbox" name="is_public" id="is_public" value="1" {{ $user->is_public ? 'checked' : '' }} class="form-check-input" />
                <label class="form-check-label" for="is_public">Yes, make profile public</label>
            </div>
        </div>

        <!-- Botão para atualizar perfil -->
        <button type="submit" class="btn btn-custom w-100">Update Profile</button>
    </form>

    <!-- Formulário para alterar senha -->
    <form action="" method="POST" class="border p-4 rounded shadow-sm mt-4">
        @csrf
        @method('PUT')

        <h3>Change Password</h3>

        <!-- Senha Antiga -->
        <div class="form-group mb-3">
            <label for="old_password" class="form-label">Old Password:</label>
            <input type="password" name="old_password" id="old_password" class="form-control" required />
        </div>

        <!-- Nova Senha -->
        <div class="form-group mb-3">
            <label for="new_password" class="form-label">New Password:</label>
            <input type="password" name="new_password" id="new_password" class="form-control" required />
        </div>

        <!-- Confirmar Nova Senha -->
        <div class="form-group mb-3">
            <label for="new_password_confirmation" class="form-label">Confirm New Password:</label>
            <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="form-control" required />
        </div>

        <!-- Botão para alterar a senha -->
        <button type="submit" class="btn btn-custom w-100">Update Password</button>
    </form>
</div>
@endsection
