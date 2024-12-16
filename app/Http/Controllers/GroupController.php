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
            'description' => 'nullable|string',         // Descrição
            'visibility' => 'required|boolean',         // Visibilidade
            'is_public' => 'required|boolean',          // Se o grupo é público
        ]);

        // Criar o grupo no banco de dados
        $group = Group::create([
            'group_name' => $validated['group_name'],
            'description' => $validated['description'],
            'owner_id' => Auth::id(),  // Usuário autenticado
            'visibility' => $validated['visibility'],
            'is_public' => $validated['is_public'],
        ]);

        return redirect()->route('group.show', $group->id);
    }

    // Mostrar um grupo específico
    public function show($id)
    {
        $group = Group::findOrFail($id);
        return view('pages.group', compact('group'));
    }
}
