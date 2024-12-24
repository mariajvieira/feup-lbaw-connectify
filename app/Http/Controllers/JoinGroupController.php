<?php

namespace App\Http\Controllers;

use App\Models\JoinGroupRequest;
use App\Models\Group;
use App\Http\Controllers\GroupController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class JoinGroupController extends Controller
{
    // Enviar pedido de adesão
    public function joinPrivateGroup(Request $request)
    {
        $validated = $request->validate([
            'group_id' => 'required|exists:group_,id', // Verificar se o grupo existe
        ]);
    
        $userId = Auth::id();
        $groupId = $validated['group_id'];
    
        // Verificar se já existe um pedido pendente
        $existingRequest = JoinGroupRequest::where('group_id', $groupId)
            ->where('user_id', $userId)
            ->where('request_status', 'pending')
            ->first();
    

        if ($existingRequest) {
            // Se o pedido já foi enviado, redireciona de volta para a página do grupo com uma mensagem
            return redirect()->route('group.show', ['groupId' => $groupId])
                ->with('success', 'Já existe um pedido pendente.');
        }
    
        // Criar novo pedido
        JoinGroupRequest::create([
            'group_id' => $groupId,
            'user_id' => $userId,
            'request_status' => 'pending', // Status inicial do pedido
        ]);
    
        // Recuperar o grupo para exibir os dados na view
        $group = Group::findOrFail($groupId);  // Recupera o grupo
        $members = $group->users();  // Relacionamento 'users' no modelo Group
        $posts = $group->posts();  // Relacionamento 'posts' no modelo Group
        $friends = Auth::user()->friends;  // Se o relacionamento de amigos estiver configurado no modelo User
    
        // Passar as variáveis para a view
        return view('pages.group', compact('group', 'members', 'posts', 'friends'))
            ->with('success', 'Pedido de adesão enviado com sucesso.');
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
            'status' => 'required|in:accepted,denied,pending',  // Certifique-se de que esses valores estão corretos
        ]);
        
        // Encontrar o pedido de adesão
        $joinRequest = JoinGroupRequest::findOrFail($id);
        
        // Verificar se o utilizador logado é o proprietário do grupo
        $group = $joinRequest->group;
        if ($group->owner_id !== auth()->id()) {
            return redirect()->route('home')->with('error', 'Você não tem permissão para aprovar ou rejeitar este pedido.');
        }
        
        // Atualizar o status do pedido
        $joinRequest->request_status = $validated['status']; // Garantir que o status é em maiúsculas (ou o formato correto)
        $joinRequest->save();
        
        // Se o pedido for aceito, adicionar o membro ao grupo
        if ($joinRequest->request_status == 'accepted') { 
            // Adicionando o utilizador à tabela group_member
            DB::table('group_member')->insert([
                'group_id' => $joinRequest->group_id,
                'user_id' => $joinRequest->user_id,
            ]);
        }
        
        // Excluir o pedido de adesão
        $joinRequest->delete();
        
        // Redirecionar de volta para a página de gerenciamento de pedidos
        return redirect()->route('manage-requests', $group->id)
            ->with('success', 'Pedido de adesão processado com sucesso.');
    }
    

    
    

}
