@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Password Recovery</h2>
    <p>Please enter your email to receive a password recovery code.</p>

    <form method="POST" action="{{ route('sendEmail') }}">
        @csrf

        <label for="email">E-mail</label>
        <input id="email" type="email" name="email" required>

        @if ($errors->has('email'))
            <p style="color: red;">{{ $errors->first('email') }}</p>
        @endif

        <button type="submit">Send Recovery Code</button>
    </form>
</div>
@endsection
