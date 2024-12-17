@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Password Recovery</h2>
    <p>The recovery code was correct. Please enter a new password.</p>

    <form method="POST" action="{{ route('resetPassword') }}">
        @csrf

        <input type="hidden" name="email" value="{{ $email }}">

        <label for="password">New Password</label>
        <input id="password" type="password" name="password" required>

        @if ($errors->has('password'))
            <span class="error">
                {{ $errors->first('password') }}
            </span>
        @endif

        <label for="password_confirmation">Confirm Password</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required>

        <button type="submit">Reset Password</button>
    </form>
</div>
@endsection
