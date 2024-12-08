<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reaction;
use Illuminate\Support\Facades\Auth;

class ReactionController extends Controller
{
    /**
     * Store a newly created reaction.
     */
    public function store(Request $request)
    {
        // Validação dos dados recebidos
        $request->validate([
            'target_id' => 'required|integer',
            'target_type' => 'required|string|in:post,comment',
            'reaction_type' => 'required|string|in:like,laugh,cry,applause,shocked',
        ]);

        try {
            // Chamar a função add_reaction
            DB::select('SELECT add_reaction(?, ?, ?, ?)', [
                $request->target_id,
                Auth::id(),
                $request->target_type,
                $request->reaction_type,
            ]);

            return response()->json(['message' => 'Reação registrada com sucesso.'], 201);
        } catch (\Exception $e) {
            \Log::error('Erro ao registrar a reação: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao registrar a reação.'], 400);
        }
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
