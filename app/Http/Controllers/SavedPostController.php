<?php
namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class SavedPostController extends Controller
{   

    public function toggleSave(Request $request)
    {
        $user = auth()->user();
        $postId = $request->input('post_id');
    
        // Verifica se o post já está salvo
        $savedPost = SavedPost::where('user_id', $user->id)
                              ->where('post_id', $postId)
                              ->first();
    
        if ($savedPost) {
            // Se já estiver salvo, remove da base de dados
            $savedPost->delete();
            return response()->json(['saved' => false]);
        } else {
            // Caso contrário, salva o post
            SavedPost::create([
                'user_id' => $user->id,
                'post_id' => $postId
            ]);
            return response()->json(['saved' => true]);
        }
    }
    
}

    
}
