@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Password Recovery</h2>
    <p>We have sent a password recovery code to your email exampleuser@gmail.com. Please check your inbox and enter the code below to reset your password.</p>

    <form method="POST" action="{{ route('verifyCode') }}">
        @csrf
        
        <input type="hidden" name="email" value="{{ session('reset_email') }}">

        <label for="code">Enter the recovery code</label>
        <input id="code" type="text" name="code" required>
        @if ($errors->has('code'))
            <span class="error">
                {{ $errors->first('code') }}
            </span>
        @endif

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
