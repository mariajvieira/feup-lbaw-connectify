<?php

namespace App\Http\Controllers;
use App\Http\Controllers\PostController;
use Illuminate\Http\Request;
use App\Models\Reaction;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class ReactionController extends Controller
{
    /**
     * Store a newly created reaction.
     */
    public function store(Request $request, $id)
    {
        // Validar apenas o tipo de reação
        $validated = $request->validate([
            'reaction_type' => 'required|in:like,laugh,cry,applause,shocked',
        ]);
    
        // Obter o usuário autenticado
        $userId = auth()->id();
    
        // Verificar se o post existe
        $postExists = Post::where('id', $id)->exists();
        if (!$postExists) {
            return response()->json(['error' => 'Post não encontrado.'], 404);
        }
    
        // Verificar se o usuário já reagiu ao post
        $reaction = Reaction::where('user_id', $userId)
                            ->where('target_id', $id)
                            ->where('target_type', 'post')
                            ->first();
    
        if ($reaction) {
            // Atualizar reação existente
            $reaction->update(['reaction_type' => $validated['reaction_type']]);
        } else {
            // Criar nova reação
            Reaction::create([
                'user_id' => $userId,
                'target_id' => $id,
                'reaction_type' => $validated['reaction_type'],
                'target_type' => 'post',
            ]);
        }
    
        return response()->json(['message' => 'Reação registada com sucesso.']);
    }
    
    
    

    /**
     * Remove the specified reaction.
     */
    public function destroy(Request $request)
    {
        // Validação dos dados recebidos
        $request->validate([
            'target_type' => 'required|string|in:post,comment',
            'target_id' => 'required|integer',
        ]);

        $reaction = Reaction::where([
            'target_type' => $request->target_type,
            'target_id' => $request->target_id,
            'user_id' => auth()->id(),
        ])->first();

        // Verificar se a reação existe
        if (!$reaction) {
            return response()->json(['error' => 'Reação não encontrada.'], 404);
        }

        // Apagar a reação
        $reaction->delete();

        return response()->json(['message' => 'Reação removida com sucesso.'], 200);
    }
}
