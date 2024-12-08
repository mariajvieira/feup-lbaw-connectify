<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class ReactionController extends Controller
{
    /**
     * Store a newly created reaction.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'target_id' => 'required|exists:posts,id',
            'reaction_type' => 'required|in:like,laugh,cry,applause,shocked',
            'target_type' => 'required|in:post',
        ]);
    
        // Verificar se o usuário já reagiu ao post
        $reaction = Reaction::where('user_id', auth()->id())
                            ->where('target_id', $validated['target_id'])
                            ->where('target_type', $validated['target_type'])
                            ->first();
    
        if ($reaction) {
            // Se já houver uma reação, atualizar a reação existente
            $reaction->reaction_type = $validated['reaction_type'];
            $reaction->save();
        } else {
            // Caso contrário, criar uma nova reação
            Reaction::create([
                'user_id' => auth()->id(),
                'target_id' => $validated['target_id'],
                'reaction_type' => $validated['reaction_type'],
                'target_type' => $validated['target_type'],
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

        // Deletar a reação
        $reaction->delete();

        return response()->json(['message' => 'Reação removida com sucesso.'], 200);
    }
}
