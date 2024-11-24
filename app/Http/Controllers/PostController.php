<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\User;
use App\Models\Group;

class PostController extends Controller
{ 
    

    public function show($id)
    {
        $post = Post::find($id);

        if (!$post) {
            return redirect()->route('home')->with('error', 'Post não encontrado.');
        }

        return view('pages.post', compact('post'));
    }

    /**
     * Creates a new post.
     */
    //public function create(Request $request)
    //{
        //$validated = $request->validate([
        //    'user_id' => 'required|exists:user_,user_id',
        //    'group_id' => 'nullable|exists:group_,group_id',
        //    'content' => 'nullable|string',
        //    'IMAGE1' => 'nullable|string',
        //    'IMAGE2' => 'nullable|string',
        //    'IMAGE3' => 'nullable|string',
        //   'is_public' => 'required|boolean',
        //]);

        //$post = new Post();
        //$post->user_id = $validated['user_id'];
        //$post->group_id = $validated['group_id'] ?? null;
        //$post->content = $validated['content'] ?? null;
        //$post->IMAGE1 = $validated['IMAGE1'] ?? null;
        //$post->IMAGE2 = $validated['IMAGE2'] ?? null;
        //$post->IMAGE3 = $validated['IMAGE3'] ?? null;
        //$post->is_public = $validated['is_public'];

        //$this->authorize('create', $post);

        //$post->save();

        //return response()->json($post, 201);
    //}
    public function create()
    {
        return view('partials.create');
    }


    //Define user timeline
    public function getPosts(Request $request)
    {
        if(!Auth::check()){ 
            $posts = Post::public()->orderBy('created_at', 'desc')->get();	
        }
        $this->authorize('getPosts', Post::class);
        $posts = Auth::user()->visiblePosts()->get();
        //TODO: view to timeline
    }

    /**
     * Updates an existing post.
     */
    public function update(Request $request, $id)
    {
        // Validar os dados do formulário
        $validated = $request->validate([
            'content' => 'nullable|string',
            'is_public' => 'required|boolean',
        ]);

        // Encontrar o post
        $post = Post::findOrFail($id);

        // Verificar se o post pertence ao usuário logado
        if ($post->user_id !== auth()->id()) {
            return redirect()->route('home')->with('error', 'Você não tem permissão para editar este post.');
        }

        // Atualizar os campos do post
        $post->content = $validated['content'] ?? $post->content;
        $post->is_public = $validated['is_public'];

        // Salvar as alterações
        $post->save();

        // Redirecionar com sucesso
        return redirect()->route('home')->with('success', 'Post atualizado com sucesso!');
    }
    

    public function edit($id)
    {
        $post = Post::findOrFail($id);

        // Verifica se o post pertence ao usuário logado
        if ($post->user_id !== auth()->id()) {
            return redirect()->route('home')->with('error', 'Você não tem permissão para editar este post.');
        }

        return view('partials.postedit', compact('post'));
    }


    /**
     * Deletes a specific post.
     */
    public function delete($id)
    {
        $post = Post::findOrFail($id);
    
        if ($post->user_id === auth()->id()) {
            $post->delete();
    
          
            return redirect()->route('user', auth()->id())->with('success', 'Post deleted successfully');
        }
    
        return redirect()->route('user', auth()->id())->with('error', 'You do not have permission to delete this post');
    }
    public function store(Request $request)
    {
        // Validação dos dados recebidos do formulário
        $validated = $request->validate([
            'content' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'is_public' => 'required|boolean',
        ]);

        // Criar o post
        $post = new Post();
        $post->user_id = auth()->id(); // Usando o ID do usuário autenticado
        $post->content = $validated['content'];
        $post->is_public = $validated['is_public'];

        // Se houver uma imagem, faz o upload
        if ($request->hasFile('image')) {
            $post->image = $request->file('image')->store('posts', 'public');
        }

        // Salva o post
        $post->save();

        // Redireciona para o home ou página do usuário
        return redirect()->route('home')->with('success', 'Post criado com sucesso!');
    }
}
