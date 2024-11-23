@extends('layouts.app')

@section('content')
<div class="profile-container">
    <h2>Perfil de {{ $user->username }}</h2>

    @if(session('error'))
        <div class="error-message">{{ session('error') }}</div>
    @endif

    <div class="profile-info">
        <p><strong>Nome:</strong> {{ $user->username }}</p>
        <p><strong>Email:</strong> {{ $user->email }}</p>
        <p><strong>Privacidade do perfil:</strong> {{ $user->is_public ? 'Público' : 'Privado' }}</p>

        <!-- Exibir imagem de perfil, se houver -->
        @if($user->profile_picture)
            <img src="{{ asset('storage/' . $user->profile_picture) }}" alt="Imagem de Perfil" class="profile-picture">
        @else
            <p><em>Sem imagem de perfil</em></p>
        @endif
    </div>

    <h3>Editar Perfil</h3>
    <form method="POST" action="{{ url('/user/' . $user->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT') 
        
        <label for="username">Nome de usuário</label>
        <input type="text" name="username" id="username" value="{{ $user->username }}" required>
        
        @if ($errors->has('username'))
            <span class="error">{{ $errors->first('username') }}</span>
        @endif

        <label for="email">Email</label>
        <input type="email" name="email" id="email" value="{{ $user->email }}" required>
        
        @if ($errors->has('email'))
            <span class="error">{{ $errors->first('email') }}</span>
        @endif

        <label for="user_password">Nova Senha</label>
        <input type="password" name="user_password" id="user_password">
        
        @if ($errors->has('user_password'))
            <span class="error">{{ $errors->first('user_password') }}</span>
        @endif

        <label for="password_confirmation">Confirmar Nova Senha</label>
        <input type="password" name="password_confirmation" id="password_confirmation">

        <label for="profilePicture">Imagem de Perfil</label>
        <input type="file" name="profilePicture" id="profilePicture">
        
        <label for="is_public">Perfil Público</label>
        <input type="checkbox" name="is_public" id="is_public" {{ $user->is_public ? 'checked' : '' }}>

        <button type="submit">Salvar Alterações</button>
    </form>

    <h3>Excluir Conta</h3>
    <form method="POST" action="{{ url('/user/' . $user->id) }}">
        @csrf
        @method('DELETE')
        <button type="submit" class="button-danger">Excluir Conta</button>
    </form>
</div>
@endsection
