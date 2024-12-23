<?php

namespace App\Http\Controllers;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class CommentController extends Controller
{
// Create comment
    public function store(Request $request, $postId)
    {
        $request->validate([
            'comment' => 'required|string|max:255',
        ]);
    
        $comment = new Comment();
        $comment->post_id = $postId;
        $comment->user_id = auth()->id();
        $comment->comment_content = $request->comment;
        $comment->save();
    
        $comment->load('user'); // Carregar os dados do usuário
    
        return response()->json([
            'message' => 'Comment posted successfully.',
            'comment' => $comment
        ]);
    }


//edit comment
    public function update(Request $request, $commentId)
    {
        $validated = $request->validate([
            'content' => 'required|string|min:1|max:255', 
        ]);
    
        $comment = Comment::findOrFail($commentId);
    
        $comment->comment_content = $validated['content'];
        $comment->save();
    
        return response()->json([
            'success' => true,
            'content' => $comment->comment_content,
        ]);
    }



// Get post comments
    public function getComments($postId)
    {
        $comments = Comment::where('post_id', $postId)->get();
    
        return response()->json([
            'comments' => $comments
        ]);
    }

    public function destroy($commentId)
    {
        $comment = Comment::find($commentId);

        if (!$comment) {
            return redirect()->route('home')->with('error', 'Comemnt not found.');
        }

        $comment->delete();
        return response()->json(['message' => 'Comentário excluído com sucesso.'], 200); 
    }
    


    public function searchComments(Request $request)
    {
        $input = $request->get('search') ? $request->get('search').':*' : "*";

        $visiblePosts = Post::where('is_public', true)->pluck('id')->toArray();
        $comments = Comment::whereRaw("tsvectors @@ to_tsquery(?)", [$input])
                            ->orderByRaw("ts_rank(tsvectors, to_tsquery(?)) ASC", [$input])
                            ->whereIn('post_id', $visiblePosts)->get();

        return response()->json($comments, 200);
    }


    public function getCommentReactionCount($commentId)
    {
        $comment = Comment::findOrFail($commentId);
        
        $reactionCount = $comment->reactions()->count();
    
        // Retorna a contagem de reações como JSON
        return response()->json([
            'reactionCount' => $reactionCount
        ]);
    }




    function getReactionIcon($type)
    {
        $icons = [
            'like' => 'fa-heart',
            'laugh' => 'fa-face-laugh-squint',
            'cry' => 'fa-face-sad-cry',
            'applause' => 'fa-hands-clapping',
            'shocked' => 'fa-face-surprise'
        ];

        return $icons[$type] ?? 'fa-smile';
    }
}
