<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SavedPostController extends Controller
{
    // Salvar o post
    public function savePost(Request $request)
    {
        $user = Auth::user();
        $post = Post::find($request->post_id);

        if ($post && !$user->savedPosts->contains($post)) {
            $user->savedPosts()->attach($post); // Adiciona o post ao usuário
            return response()->json(['message' => 'Post salvo com sucesso!', 'saved' => true]);
        }

        return response()->json(['message' => 'Este post já está salvo.'], 400);
    }

    // Remover o post salvo
    public function removeSavePost(Request $request)
    {
        $user = Auth::user();
        $post = Post::find($request->post_id);

        if ($post && $user->savedPosts->contains($post)) {
            $user->savedPosts()->detach($post); // Remove o post da lista de salvos
            return response()->json(['message' => 'Post removido com sucesso!', 'saved' => false]);
        }

        return response()->json(['message' => 'Este post não estava salvo.'], 400);
    }
}
