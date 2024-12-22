<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class GroupController extends Controller
{
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

    use Illuminate\Support\Facades\Log;

    public function index()
    {
        $user = auth()->user();
    
        $groupsAsMember = $user->groups;
        Log::info('Groups as member:', $groupsAsMember->toArray());
    
        $ownedGroups = $user->ownedGroups;
        Log::info('Owned groups:', $ownedGroups->toArray());
    
        $allGroups = $groupsAsMember->merge($ownedGroups);
        Log::info('All groups:', $allGroups->toArray());
    
        return view('layouts.app', compact('allGroups'));
    }
}
