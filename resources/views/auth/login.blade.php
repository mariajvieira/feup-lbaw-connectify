@extends('layouts.app')

@section('content')
<form method="POST" action="{{ route('login') }}">
    {{ csrf_field() }}

    <!-- Campo genérico para Username ou E-mail -->
    <label for="login">Username ou E-mail</label>
    <input id="login" type="text" name="login" value="{{ old('login') }}" required autofocus>
    @if ($errors->has('login'))
        <span class="error">
          {{ $errors->first('login') }}
        </span>
    @endif

    <!-- Campo de senha -->
    <label for="password">Password</label>
    <input id="password" type="password" name="password" required>
    @if ($errors->has('password'))
        <span class="error">
            {{ $errors->first('password') }}
        </span>
    @endif

    <!-- Opção para lembrar o login -->
    <label>
        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> Remember Me
    </label>

    <!-- Botões de ação -->
    <button type="submit">
        Login
    </button>
    <a class="button button-outline" href="{{ route('register') }}">Register</a>

    <!-- Mensagem de sucesso -->
    @if (session('success'))
        <p class="success">
            {{ session('success') }}
        </p>
    @endif
</form>
@endsection
