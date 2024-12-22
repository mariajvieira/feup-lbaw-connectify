@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
                    <h4 class="mb-0">Password Recovery</h4>
                    <p class=" mb-4">We have sent a password recovery code to <strong>{{ session('reset_email') }}</strong>. Please check your inbox and enter the code below to proceed.</p>

                    <form method="POST" action="{{ route('verifyCode') }}">
                        @csrf

                        <input type="hidden" name="email" value="{{ session('reset_email') }}">

                        <div class="mb-4">
                            <label for="code" class="form-label">Enter the recovery code</label>
                            <input id="code" type="text" class="form-control" name="code" required>
                            
                            @if ($errors->has('code'))
                                <div class="text-danger mt-2">
                                    {{ $errors->first('code') }}
                                </div>
                            @endif
                        </div>

                        <button type="submit" class="btn btn-custom w-100 py-2">Verify Code</button>
                    </form>
        </div>
    </div>
</div>
@endsection
