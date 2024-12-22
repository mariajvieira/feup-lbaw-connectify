@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
                    <h4 class="mb-0">Reset Password</h4>
                    <p class="mb-4">The recovery code was correct. Please enter a new password.</p>

                    <form method="POST" action="{{ route('resetPassword') }}">
                        @csrf

                        <input type="hidden" name="email" value="{{ $email }}">

                        <div class="mb-4">
                            <label for="password" class="form-label">New Password</label>
                            <input id="password" type="password" class="form-control" name="password" required>

                            @if ($errors->has('password'))
                                <div class="text-danger mt-2">
                                    {{ $errors->first('password') }}
                                </div>
                            @endif
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <input id="password_confirmation" type="password" class="form-control" name="password_confirmation" required>
                        </div>

                        <button type="submit" class="btn btn-custom w-100 py-2">Reset Password</button>
                    </form>
            </div>
        </div>
    </div>
</div>
@endsection
