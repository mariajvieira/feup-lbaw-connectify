@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Password Recovery</h2>
    <p>We have sent a password recovery code to {{ session('reset_email') }}. Please check your inbox and enter the code below to proceed.</p>

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

        <button type="submit">Verify Code</button>
    </form>
</div>
@endsection
