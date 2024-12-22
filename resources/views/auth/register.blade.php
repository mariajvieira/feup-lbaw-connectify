@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 rounded-lg">
                <div class="card-body">
                    <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
                        {{ csrf_field() }}

                        <div class="mb-4">
                            <label for="username" class="form-label">Username</label>
                            <input id="username" type="text" class="form-control" name="username" value="{{ old('username') }}" required autofocus>
                            @if ($errors->has('username'))
                                <div class="text-danger">
                                    {{ $errors->first('username') }}
                                </div>
                            @endif
                        </div>

                        <div class="mb-4">
                            <label for="email" class="form-label">E-mail Address</label>
                            <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required>
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

                        <div class="mb-4">
                            <label for="password-confirm" class="form-label">Confirm Password</label>
                            <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>
                        </div>

                        <button type="submit" class="btn btn-custom w-100 mb-3 py-2">Register</button>

                        <div class="d-flex justify-content-between text-center mt-3">
                            <p class="mb-0">Already have an account?</p>
                            <a class="btn text-custom py-2" href="{{ route('login') }}">Login</a>
                        </div>
                    </form>

                    <div class="text-center mt-3">
                        <a href="{{ route('google-auth') }}" class="btn btn-light btn-outline-dark w-100 py-2">Continue with <i class="fa-brands fa-google"></i>oogle</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
