<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Group;
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
    public function show($id)
    {
        $group = Group::findOrFail($id);
        return view('pages.group', compact('group'));
    }

    // Mostrar todos os grupos no feed principal
    public function index()
    {
        // Obtém o usuário logado
        $user = auth()->user();
        
        // Obtém os grupos aos quais o usuário pertence como membro
        $groupsAsMember = $user->groups;  // Relacionamento com a tabela pivot 'group_member'
        
        // Obtém os grupos onde o usuário é proprietário
        $ownedGroups = $user->ownedGroups;  // Relacionamento com a tabela pivot 'group_owner'

        // Combina os grupos de membros e administradores
        $allGroups = $groupsAsMember->merge($ownedGroups);

        // Obtém os posts visíveis do usuário e dos seus amigos
        $posts = $user->visiblePosts(); 

        // Retorna a view com todos os grupos e posts
        return view('pages.home', compact('posts', 'allGroups'));
    }

    // Função para permitir que o usuário entre em um grupo público
    public function joinPublicGroup($groupId)
{
    $group = Group::findOrFail($groupId);

    // Verifica se o grupo é público
    if (!$group->is_public) {
        return response()->json(['message' => 'This is not a public group'], 400);
    }

    // Adiciona o usuário ao grupo, se não for o dono do grupo e não for membro
    if (!$group->users->contains(Auth::user()->id)) {
        $group->users()->attach(Auth::id());

        return response()->json(['message' => 'Successfully joined the group!'], 200);
    }

    return response()->json(['message' => 'You are already a member of this group.'], 400);
}

}
