<?php
namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FeedController extends Controller
{
    /**
     * Exibe os posts públicos e os dos amigos.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Obtém o usuário logado
        $user = auth()->user();

        // Obtém os posts públicos
        $publicPosts = Post::where('is_public', true)
            ->orderBy('post_date', 'desc')
            ->get();

        // Obtém os IDs dos amigos diretamente com uma consulta
        $friendIds = DB::table('friendship')
            ->where('user_id1', $user->id)
            ->orWhere('user_id2', $user->id)
            ->pluck('user_id1', 'user_id2')
            ->flatten()
            ->unique();

        // Adiciona o próprio usuário para garantir que seus próprios posts também apareçam
        $friendIds->push($user->id);

        // Agora, obtém os posts dos amigos
        $friendPosts = Post::whereIn('user_id', $friendIds)
            ->orderBy('post_date', 'desc')
            ->get();

        // Combina os posts públicos e os posts dos amigos
        $posts = $publicPosts->merge($friendPosts);

        // Ordena todos os posts pela data (descendente)
        $posts = $posts->sortByDesc('post_date');

        // Retorna a view com todos os posts
        return view('pages.feed', compact('posts'));
    }
}
