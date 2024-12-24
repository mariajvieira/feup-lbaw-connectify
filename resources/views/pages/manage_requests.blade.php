@extends('layouts.app')

@section('content')
<h3>Join Requests</h3>

@foreach($requests as $request)
    <div>
        <p>User:: {{ $request->user->username }}</p>
        
        <!-- Formulário para aceitar ou rejeitar o pedido -->
        <form action="{{ route('handle-group-request', $request->id) }}" method="POST">
            @csrf
            <button type="submit" name="status" value="accepted">Accept</button>
            <button type="submit" name="status" value="denied">Reject</button>
        </form>
    </div>
@endforeach
@endsection
