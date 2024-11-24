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
    
        // Obtém os posts visíveis (do próprio usuário, amigos e públicos)
        $posts = $user->Friends_Public_Posts();  // Chamando a função visiblePosts() que retorna os posts
    
        // Retorna a view com os posts
        return view('pages.home', compact('posts'));
    }
}
