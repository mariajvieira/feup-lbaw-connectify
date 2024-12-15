<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;

class UserSearchController extends Controller
{
    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|max:255',
        ]);

        $query = $request->input('query');

        // Primeiro, tentamos um Exact Match Search para usuários
        $usersExactMatch = User::where('username', 'like', '%' . $query . '%')
            ->orWhere('email', 'like', '%' . $query . '%')
            ->where('is_public', true)
            ->get();

        // Se não encontrar resultados exatos, tenta um Full Text Search
        $usersFullText = $usersExactMatch->isEmpty() 
            ? User::where('is_public', true)
                ->whereRaw("to_tsvector('english', username) @@ plainto_tsquery('english', ?)", [$query])
                ->get()
            : $usersExactMatch;


        // Agora, fazemos a busca de posts se necessário
        // Como não temos a coluna 'title', usamos 'content' para a busca
        $postsExactMatch = Post::where('content', 'like', '%' . $query . '%')
            ->get();

        // Se não encontrar resultados exatos, tenta um Full Text Search para posts
        $postsFullText = $postsExactMatch->isEmpty() 
            ? Post::whereRaw("to_tsvector('english', content) @@ plainto_tsquery('english', ?)", [$query])
                ->get()
            : $postsExactMatch;




        $commentsExactMatch = Comment::where('comment_content', 'like', '%' . $query . '%')
            ->get();

        // Se não encontrar resultados exatos, tenta um Full Text Search para posts
        $commentsFullText = $commentsExactMatch->isEmpty() 
            ? Post::whereRaw("to_tsvector('english', content) @@ plainto_tsquery('english', ?)", [$query])
                ->get()
            : $commentsExactMatch;



        // Retornamos a view com os resultados
        return view('partials.search', compact('usersFullText', 'postsFullText', 'commentsFullText', 'query'));
    }
}