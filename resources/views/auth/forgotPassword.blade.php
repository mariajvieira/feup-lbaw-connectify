@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 ">
                <h4 class="mb-0">Password Recovery</h4>

                    <p>Please enter your email to receive a password recovery code.</p>

                    <form method="POST" action="{{ route('sendEmail') }}">
                        @csrf

                        <div class="mb-4">
                            <label for="email" class="form-label">E-mail</label>
                            <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required autofocus>

                            @if ($errors->has('email'))
                                <div class="text-danger mt-2">
                                    {{ $errors->first('email') }}
                                </div>
                            @endif
                        </div>

                        <button type="submit" class="btn btn-custom w-100 mb-3 py-2">Send Recovery Code</button>
                    </form>
            </div>
        </div>
    </div>
</div>
@endsection
