@extends('layouts.app')

@section('content')
<form method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
    {{ csrf_field() }}

    <h2>Register</h2>
    <label for="profile_picture">Profile Picture</label>
  <div>
    @if (isset($user) && $user->profile_picture)
        <img src="{{ asset($user->profile_picture) }}" alt="Profile Picture" style="max-width: 150px; max-height: 150px; display: block; margin-bottom: 10px;">
    @endif
    <input type="file" name="profile_picture" id="profile_picture" value="{{ old('profile_picture') }}">
    @if ($errors->has('profile_picture'))
        <span class="error">
            {{ $errors->first('profile_picture') }}
        </span>
    @endif
</div>

    <label for="username">Username</label>
    <input id="username" type="text" name="username" value="{{ old('username') }}" required autofocus>
    @if ($errors->has('username'))
      <span class="error">
          {{ $errors->first('username') }}
      </span>
    @endif

    <label for="email">E-Mail Address</label>
    <input id="email" type="email" name="email" value="{{ old('email') }}" required>
    @if ($errors->has('email'))
      <span class="error">
          {{ $errors->first('email') }}
      </span>
    @endif

    <label for="password">Password</label>
    <input id="password" type="password" name="password" required>
    @if ($errors->has('password'))
      <span class="error">
          {{ $errors->first('password') }}
      </span>
    @endif

    <label for="password-confirm">Confirm Password</label>
    <input id="password-confirm" type="password" name="password_confirmation" required>

    <button type="submit">
      Register
    </button>
    <a class="button button-outline" href="{{ route('login') }}">Login</a>
</form>
@endsection
