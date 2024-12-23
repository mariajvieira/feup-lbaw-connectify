<?php

namespace App\Http\Controllers;
use App\Http\Controllers\PostController;
use Illuminate\Http\Request;
use App\Models\Reaction;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class ReactionController extends Controller
{
    /**
     * Store a newly created reaction.
     */
    public function storepost(Request $request, $id)
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
            $reaction = Reaction::create([
                'user_id' => $userId,
                'target_id' => $id,
                'reaction_type' => $validated['reaction_type'],
                'target_type' => 'post',
            ]);
        }
    
        // Certificar-se de que estamos retornando o ID correto da reação
        return response()->json([
            'message' => 'Reação registada com sucesso.',
            'reaction_id' => $reaction->id // Garantir que o ID da reação seja retornado corretamente
        ]);
    }


    public function storecomment(Request $request, $id)
    {
        // Validar apenas o tipo de reação
        $validated = $request->validate([
            'reaction_type' => 'required|in:like,laugh,cry,applause,shocked',
        ]);
    
        $userId = auth()->id();
    
        $commentExists = Comment::where('id', $id)->exists();
        if (!$commentExists) {
            return response()->json(['error' => 'Comment não encontrado.'], 404);
        }
    
        // Verificar se o usuário já reagiu ao post
        $reaction = Reaction::where('user_id', $userId)
                            ->where('target_id', $id)
                            ->where('target_type', 'comment')
                            ->first();
    
        if ($reaction) {
            $reaction->update(['reaction_type' => $validated['reaction_type']]);
        } else {
            // Criar nova reação
            $reaction = Reaction::create([
                'user_id' => $userId,
                'target_id' => $id,
                'reaction_type' => $validated['reaction_type'],
                'target_type' => 'comment',
            ]);
        }
    
        return response()->json([
            'message' => 'Reação registada com sucesso.',
            'reaction_id' => $reaction->id 
        ]);
    }
    
    
    
    /**
     * Remove the specified reaction.
     */
    public function destroy($reactionId)
    {
        // Encontrar a reação, ou retornar um erro caso não exista
        $reaction = Reaction::find($reactionId);
    
        // Verificar se a reação existe e se pertence ao usuário autenticado
        if (!$reaction || $reaction->user_id !== auth()->id()) {
            return response()->json(['error' => 'Reação não encontrada ou você não tem permissão para removê-la.'], 403);
        }
    
        // Apagar a reação
        $reaction->delete();
    
        // Retornar uma resposta JSON com status 200 e mensagem de sucesso
        return response()->json(['message' => 'Reação removida com sucesso.'], 200);
    }



}
