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
    
        // Obtém os posts do próprio usuário e dos seus amigos
        $posts = $user->visiblePosts();  // Chamando a função visiblePosts() que retorna os posts
    
        // Obtém os grupos do usuário
        $groups = $user->groups; // Assumindo que a relação 'groups' foi definida corretamente no modelo User

        // Retorna a view com os posts e grupos
        return view('pages.home', compact('posts', 'groups'));
    }
}
