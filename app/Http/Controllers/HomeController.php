<?php

namespace App\Http\Controllers;
use App\Models\Post;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Obtém o usuário logado
        $user = auth()->user();
    
        // Obtém os posts visíveis (do próprio usuário, amigos e públicos)
        $posts = $user->visiblePosts();  // Chamando a função visiblePosts() que retorna os posts
    
        // Retorna a view com os posts
        return view('pages.home', compact('posts'));
    }
    
    
    
    
    
    
    
}