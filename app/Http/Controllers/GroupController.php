<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class GroupController extends Controller
{
    // Mostrar o formulário para criar um grupo
    public function create()
    {
        return view('partials.creategroup');
    }

    // Armazenar o grupo no banco de dados
    public function store(Request $request)
    {
        $validated = $request->validate([
            'group_name' => 'required|string|max:255',  // Nome do grupo
            'description' => 'nullable|string',         // Descrição (opcional)
            'is_public' => 'required|boolean',          // Se o grupo é público ou privado
        ]);

        // Criar o grupo no banco de dados
        $group = Group::create([
            'group_name' => $validated['group_name'],
            'description' => $validated['description'],
            'owner_id' => Auth::id(),  // Usuário autenticado como proprietário
            'is_public' => $validated['is_public'],
        ]);

        // Adicionar o proprietário à tabela de membros (group_member)
        $group->users()->attach(Auth::id());

        // Redirecionar para a página do grupo
        return redirect()->route('group.show', $group->id);
    }

    // Mostrar um grupo específico
    public function show($groupId)
    {
        // Encontra o grupo ou falha
        $group = Group::with('users', 'owner')->findOrFail($groupId);
        $members = $group->users; // Membros do grupo
    
        // Passa os dados para a view
        return view('pages.group', compact('group', 'members'));
    }
    
    public function viewMembers($groupId)
    {
        $group = Group::with('users')->findOrFail($groupId); // Carrega os usuários
        $members = $group->users;
    
        // Obter amigos apenas se o usuário autenticado for o proprietário
        $friends = [];
        if (Auth::id() == $group->owner_id) {
            // Verifica se o usuário é o dono e obtém os amigos que não são membros do grupo
            $friends = Auth::user()->friends()->whereNotIn('id', $members->pluck('id'))->get();
        }
    
        // Passa os dados para a view
        return view('pages.group_members', compact('group', 'members', 'friends'));
    }
    
    

    // Mostrar todos os grupos no feed principal
    public function index()
    {
        $user = auth()->user();

        // Obtém os grupos onde o usuário é membro ou proprietário
        $groupsAsMember = $user->groups; 
        $ownedGroups = $user->ownedGroups; 

        // Combina os grupos
        $allGroups = $groupsAsMember->merge($ownedGroups);

        // Obtém posts visíveis do usuário
        $posts = $user->visiblePosts(); 

        return view('pages.home', compact('posts', 'allGroups'));
    }

    // Permitir que o usuário entre em um grupo público
    public function joinPublicGroup($groupId)
    {
        $group = Group::findOrFail($groupId);

        // Verifica se o grupo é público
        if (!$group->is_public) {
            return response()->json(['message' => 'This is not a public group'], 400);
        }

        // Verifica se o usuário não é membro
        if (!$group->users->contains(Auth::user()->id)) {
            $group->users()->attach(Auth::id());

            return response()->json(['message' => 'Successfully joined the group!'], 200);
        }

        return response()->json(['message' => 'You are already a member of this group.'], 400);
    }

 
    // Deixar um grupo
    public function leaveGroup($groupId)
    {
        $group = Group::findOrFail($groupId);

        // Verifica se o usuário é membro
        if ($group->users->contains(Auth::id())) {
            $group->users()->detach(Auth::id());

            return redirect()->route('group.show', $group->id)->with('message', 'You have left the group successfully.');
        }

        return redirect()->route('group.show', $group->id)->with('error', 'You are not a member of this group.');
    }

    // Remover um membro do grupo
    public function removeMember($groupId, $userId)
    {
        $group = Group::findOrFail($groupId);
        $user = User::findOrFail($userId);
    
        // Verifica se o usuário é o owner
        if ($group->owner_id == $user->id) {
            return redirect()->route('group.show', $groupId)->with('error', 'You cannot remove the owner of the group.');
        }
    
        // Remove o usuário do grupo
        $group->users()->detach($userId); 
    
        return redirect()->route('group.show', $groupId)->with('success', 'Member removed successfully.');
    }

    // Adicionar amigo ao grupo
    public function addFriendToGroup(Request $request, $groupId)
{
    $group = Group::findOrFail($groupId);

    // Verifica se o usuário logado é o owner do grupo
    if (Auth::id() !== $group->owner_id) {
        return redirect()->route('group.show', $groupId)->with('error', 'Only the owner can add members to this group.');
    }

    // Verificar se o amigo foi selecionado
    $friendId = $request->input('friend_id');
    $friend = User::findOrFail($friendId);

    // Verifica se o amigo já está no grupo
    if ($friend->isInGroup($groupId)) {
        return redirect()->route('group.show', $groupId)->with('error', 'This friend is already in the group.');
    }

    // Adiciona o amigo ao grupo
    $group->users()->attach($friendId);

    return redirect()->route('group.show', $groupId)->with('success', 'Friend added to the group successfully.');
}


}
