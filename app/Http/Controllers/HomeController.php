<?php

namespace App\Http\Controllers;

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
        // Pega o usuário logado
        $user = auth()->user();
    
        // Se o usuário não estiver logado, redireciona para a página de login
        if (!$user) {
            return redirect()->route('login')->with('error', 'Por favor, faça login');
        }
    
        // Buscar os posts visíveis (do próprio usuário, amigos e posts públicos)
        $posts = $user->visiblePosts(); // Aqui estamos utilizando o método que você criou no modelo
    
     
        // Se não houver posts, retornar à home com uma mensagem
        if ($posts->isEmpty()) {
            return view('home')->with('message', 'Você não tem amigos ou seus amigos não postaram ainda.');
        }
    
        // Passar os posts para a view
        return view('pages.home', compact('posts'));
    }
    
    
}