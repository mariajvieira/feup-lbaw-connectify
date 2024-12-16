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
        // Validação da entrada
        $request->validate([
            'query' => 'required|string|max:255',
            'filter-date' => 'nullable|date', // Validação para a data
        ]);

        $query = $request->input('query');
        $filterDate = $request->input('filter-date'); // Filtro de data

        // Busca exata para usuários
        $usersExactMatch = User::where('username', 'like', '%' . $query . '%')
            ->orWhere('email', 'like', '%' . $query . '%')
            ->where('is_public', true)
            ->get();

        // Full Text Search para usuários, se não encontrar resultados exatos
        $usersFullText = $usersExactMatch->isEmpty() 
            ? User::where('is_public', true)
                ->whereRaw("to_tsvector('english', username) @@ plainto_tsquery('english', ?)", [$query])
                ->orWhereRaw("to_tsvector('english', email) @@ plainto_tsquery('english', ?)", [$query])
                ->get()
            : $usersExactMatch;

        // Busca exata para posts
        $postsQuery = Post::where('content', 'like', '%' . $query . '%');

        // Aplica o filtro de data, se fornecido
        if ($filterDate) {
            $postsQuery->whereDate('post_date', '=', $filterDate);
        }

        $postsFullText = $postsQuery->get();

        // Busca exata para comentários
        $commentsExactMatch = Comment::where('comment_content', 'like', '%' . $query . '%')
            ->get();

        // Full Text Search para comentários, se não encontrar resultados exatos
        $commentsFullText = $commentsExactMatch->isEmpty() 
            ? Comment::whereRaw("to_tsvector('english', comment_content) @@ plainto_tsquery('english', ?)", [$query])
                ->get()
            : $commentsExactMatch;


        $commentsQuery = Comment::where('comment_content', 'like', '%' . $query . '%');
        if ($filterDate) {
            $commentsQuery->whereDate('commentDate', '=', $filterDate);
        }
        
        // Retorna a view com os resultados
        return view('partials.search', compact('usersFullText', 'postsFullText', 'commentsFullText', 'query', 'filterDate'));
    }
}
