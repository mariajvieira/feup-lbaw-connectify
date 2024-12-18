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
        $comment->comment_content = $request->comment;
        $comment->post_id = $postId;
        $comment->user_id = auth()->id();
        $comment->save();

        /*return response()->json([
            'id' => $comment->id,
            'comment_content' => $comment->comment_content,
            'user' => [
                'username' => $comment->user->username,
            ],
            'post_id' => $comment->post_id,
        ], 201);*/

        return redirect()->route('home')->with('success', 'Comment posted successfully!');
    }

//edit comment
    public function editComment(Request $request, $commentId)
    {

        $comment = Comment::find($commentId);
        $this->authorize('edit', $comment);

        if (!$comment) {
            return response()->json(['message' => 'Comment not found'], 404);
        }

        $comment->comment_content = $request->input('content'); 
        $comment->save();

        return response()->json(['message' => 'Comment updated successfully'], 200);
    }

// Get post comments
    public function getComments($postId)
    {
        $post = Post::find($postId);
        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $comments = Comment::where('post_id', $postId)
            ->select('id', 'comment_content as content', 'commentDate', 'user_id as userId')
            ->orderBy('commentDate', 'desc')
            ->get();

        return response()->json($comments, 200);
    }

    public function destroy($commentId)
    {
        $comment = Comment::find($commentId);

        if (!$comment) {
            return redirect()->route('home')->with('error', 'Comemnt not found.');
        }

        $comment->delete();
            //return response()->json(['message' => 'Comentário excluído com sucesso.'], 200); // Definido explicitamente o status 200
        return redirect()->route('home')->with('success', 'Comment successfully deleted!');
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
}
