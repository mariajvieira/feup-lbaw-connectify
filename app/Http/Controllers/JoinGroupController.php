<?php

namespace App\Http\Controllers;

use App\Models\JoinGroupRequest;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class JoinGroupController extends Controller
{
    // Enviar pedido de adesão
    public function joinPrivateGroup(Request $request)
    {
        $validated = $request->validate([
            'group_id' => 'required|exists:group_,id',
        ]);
    
        $userId = Auth::id();
    
        // Verificar se já existe um pedido pendente
        $existingRequest = JoinGroupRequest::where('group_id', $validated['group_id'])
            ->where('user_id', $userId)
            ->where('request_status', 'pending') // Valor corrigido (uppercase)
            ->first();
    
        if ($existingRequest) {
            return response()->json(['message' => 'Já existe um pedido pendente.'], 400);
        }
    
        // Criar novo pedido
        JoinGroupRequest::create([
            'group_id' => $validated['group_id'],
            'user_id' => $userId,
            'request_status' => 'pending', // Valor corrigido (uppercase)
        ]);
    
        return response()->json(['message' => 'Pedido enviado com sucesso.'], 201);
    }
    
    // Listar pedidos pendentes para o owner do grupo
    // Listar pedidos pendentes para o owner do grupo
public function listGroupRequests($groupId)
{
    $group = Group::findOrFail($groupId);

    // Verificar se o utilizador logado é o owner do grupo
    if ($group->owner_id !== auth()->id()) {
        return redirect()->route('home')->with('error', 'Você não tem permissão para ver os pedidos.');
    }

    // Buscar pedidos de adesão pendentes
    $requests = JoinGroupRequest::where('group_id', $groupId)
        ->where('request_status', '=', 'pending') // Adicionar '=' explícito
        ->with('user')
        ->get();

    return view('pages/manage_requests', compact('requests'));
}


    // Aceitar ou rejeitar pedido
    public function handleGroupRequest(Request $request, $id)
{
    // Validação do status
    $validated = $request->validate([
        'status' => 'required|in:approved,rejected',  // Certifique-se de que esses valores estão corretos
    ]);
    
    // Encontrar o pedido de adesão
    $joinRequest = JoinGroupRequest::findOrFail($id);
    
    // Verificar se o utilizador logado é o proprietário do grupo
    $group = $joinRequest->group;
    if ($group->owner_id !== auth()->id()) {
        return redirect()->route('home')->with('error', 'Você não tem permissão para aprovar ou rejeitar este pedido.');
    }
    
    // Atualizar o status do pedido
    $joinRequest->request_status = strtoupper($validated['status']); // Garantir que o status é em maiúsculas (ou o formato correto)
    $joinRequest->save();
    
    // Se aprovado, adicionar o utilizador ao grupo
    if ($joinRequest->request_status == 'approved') { // Usando 'APPROVED' maiúsculo
        // Criar a entrada do membro no grupo
        $groupMember = new GroupMember();
        $groupMember->group_id = $joinRequest->group_id;
        $groupMember->user_id = $joinRequest->user_id;
        $groupMember->save(); // Adiciona o utilizador como membro do grupo
    }
    
    // Redirecionar de volta para a página de gerenciamento de pedidos
    return redirect()->route('manage-requests', $group->id)
        ->with('success', 'Pedido de adesão processado com sucesso.');
}

    
    

}
