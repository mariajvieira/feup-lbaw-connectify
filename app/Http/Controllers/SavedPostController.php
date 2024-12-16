<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SavedPost;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;

class SavedPostController extends Controller
{
    /**
     * Toggle a saved post.
     */
    public function toggle($id)
    {
        $post = Post::find($id);
    
        if (!$post) {
            return response()->json(['error' => 'Post not found'], 404);
        }
    
        $userId = auth()->id();
    
        // Verificar se o post já está salvo
        $savedPost = SavedPost::where('user_id', $userId)->where('post_id', $id)->first();
    
        if ($savedPost) {
            $savedPost->delete();
            return response()->json(['message' => 'Post unsaved successfully.', 'status' => 'removed']);
        } else {
            SavedPost::create([
                'user_id' => $userId,
                'post_id' => $id,
            ]);
            return response()->json(['message' => 'Post saved successfully.', 'status' => 'saved']);
        }
    }
    
public function isSaved($id)
{
    $userId = Auth::id();
    $isSaved = SavedPost::where('user_id', $userId)->where('post_id', $id)->exists();

    return response()->json(['isSaved' => $isSaved]);
}



}
