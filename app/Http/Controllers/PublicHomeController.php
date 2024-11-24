<?php
namespace App\Http\Controllers;

use App\Models\Post;

class PublicHomeController extends Controller
{
    public function index()
    {
        // Obtém todos os posts públicos, ordenados por data de criação
        $posts = Post::where('is_public', true)
            ->orderBy('post_date', 'desc')
            ->get();

        // Retorna a view com os posts públicos
        return view('pages.welcome', compact('posts'));
    }
}
