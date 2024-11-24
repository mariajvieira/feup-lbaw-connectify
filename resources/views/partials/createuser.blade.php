@extends('layouts.app')

@section('content')
<div class="create-user-container">
    <h2>Criar Novo Usuário</h2>

    <form action="{{ route('user.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- Nome de usuário -->
        <label for="username">Username</label>
        <input type="text" name="username" id="username" value="{{ old('username') }}" required>
        @error('username') <div class="error">{{ $message }}</div> @enderror

        <!-- Email -->
        <label for="email">Email</label>
        <input type="email" name="email" id="email" value="{{ old('email') }}" required>
        @error('email') <div class="error">{{ $message }}</div> @enderror

        <!-- Senha -->
        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>
        @error('password') <div class="error">{{ $message }}</div> @enderror

        <!-- Confirmação da senha -->
        <label for="password_confirmation">Confirm Password</label>
        <input type="password" name="password_confirmation" id="password_confirmation" required>

        <button type="submit">Create User</button>
    </form>
</div>
@endsection



