<?php

namespace App\Http\Controllers;

use App\Models\SavedPost;
use Illuminate\Http\Request;

class SavedPostController extends Controller
{
    public function toggleSave(Request $request)
    {
        // Obtém o usuário autenticado
        $user = auth()->user();
        $postId = $request->input('post_id');

        // Tenta encontrar o SavedPost correspondente ao usuário e ao post
        $savedPost = SavedPost::where('user_id', $user->id)
                              ->where('post_id', $postId)
                              ->first();

        // Se o SavedPost existir, deleta-o
        if ($savedPost) {
            // Deleta o SavedPost
            $savedPost->delete();
            return response()->json(['saved' => false]);
        } else {
            // Caso contrário, cria um novo SavedPost
            SavedPost::create([
                'user_id' => $user->id,
                'post_id' => $postId
            ]);
            return response()->json(['saved' => true]);
        }
    }
}
