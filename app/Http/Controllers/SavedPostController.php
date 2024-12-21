<?php

namespace App\Http\Controllers;

use App\Models\SavedPost;
use Illuminate\Http\Request;

class SavedPostController extends Controller
{
    public function toggleSave(Request $request)
    {
        $user = auth()->user();
        $postId = $request->input('post_id');

        $savedPost = SavedPost::where('user_id', $user->id)
                              ->where('post_id', $postId)
                              ->first();

        if ($savedPost) {
            $savedPost->delete();
            return response()->json(['saved' => false]);
        } else {
            SavedPost::create([
                'user_id' => $user->id,
                'post_id' => $postId
            ]);
            return response()->json(['saved' => true]);
        }
    }
}