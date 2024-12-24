<h3>Pedidos de Adesão</h3>

@foreach($requests as $request)
    <div>
        <p>Utilizador: {{ $request->user->username }}</p>
        
        <!-- Formulário para aceitar o pedido -->
        <form action="{{ route('handle-group-request', $request->id) }}" method="POST">
            @csrf
            <input type="hidden" name="status" value="approved">
            <button type="submit">Aceitar</button>
        </form>

        <!-- Formulário para rejeitar o pedido -->
        <form action="{{ route('handle-group-request', $request->id) }}" method="POST">
            @csrf
            <input type="hidden" name="status" value="rejected">
            <button type="submit">Rejeitar</button>
        </form>
    </div>
@endforeach
