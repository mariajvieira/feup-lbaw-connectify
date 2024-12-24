@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 rounded-lg">

                <div class="card-body">
                    <form method="POST" action="{{ route('login') }}">
                        {{ csrf_field() }}

                        <div class="mb-4">
                            <label for="email" class="form-label">E-mail</label>
                            <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required autofocus>
                            @if ($errors->has('email'))
                                <div class="text-danger">
                                    {{ $errors->first('email') }}
                                </div>
                            @endif
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <input id="password" type="password" class="form-control" name="password" required>
                            @if ($errors->has('password'))
                                <div class="text-danger">
                                    {{ $errors->first('password') }}
                                </div>
                            @endif
                        </div>

                        <div class="d-flex justify-content-between mb-4">
                            <a href="{{ route('forgotPassword') }}" class="text-decoration-none text-custom">Forgot your password?</a>
                        </div>

                        <button type="submit" class="btn btn-custom w-100 mb-3 py-2">Login</button>

                        <div class="d-flex justify-content-between mt-3 text-center">
                            <p class="mb-0">Don't have an account yet?</p>
                            <a class="btn text-custom py-2" href="{{ route('register') }}">Register</a>
                        </div>
                    </form>

                    @if (session('success'))
                        <div class="alert alert-success mt-3">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="text-center mt-3">
                        <a href="{{ route('google-auth') }}" class="btn btn-light btn-outline-dark w-100 py-2">Continue with <i class="fa-brands fa-google"></i>oogle</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
