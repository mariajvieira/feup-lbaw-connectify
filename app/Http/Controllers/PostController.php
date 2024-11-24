<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; 

class PostController extends Controller
{
    /**
     * Show a specific post.
     */
    public function show($id)
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json(['error' => 'Post não encontrado.'], 404);
        }

        return response()->json($post, 200);
    }

    public function create()
    {
        return view('partials.create');
    }


    /**
     * Store a newly created post.
     */
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

    
    
    

    public function edit($id)
    {
        $post = Post::find($id);
    
        if (!$post) {
            return redirect()->route('home')->with('error', 'Post não encontrado.');
        }
    
        return view('partials.postedit', compact('post'));  // Renderiza a view com os dados do post
    }
    

    /**
     * Update a specific post.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'content' => 'nullable|string',
            'is_public' => 'required|boolean',
        ]);
    
        $post = Post::findOrFail($id);
    
        if ($post->user_id !== Auth::id()) {
            return redirect()->route('home')->with('error', 'Você não tem permissão para editar este post.');
        }
    
        $post->update([
            'content' => $validated['content'] ?? $post->content,
            'is_public' => $validated['is_public'],
        ]);
    
        return redirect()->route('home')->with('success', 'Post atualizado com sucesso!');
    }
    

    /**
     * Delete a specific post.
     */
    public function delete($id)
    {
        $post = Post::findOrFail($id);

        if ($post->user_id === Auth::id()) {
            $post->delete();
            return response()->json(['message' => 'Post deletado com sucesso!'], 200);
        }

        return response()->json(['error' => 'Você não tem permissão para deletar este post.'], 403);
    }


    public function destroy($id)
    {
        // Verifica se o post existe
        $post = Post::find($id);

        if (!$post) {
            return redirect()->route('home')->with('error', 'Post não encontrado.');
        }

        // Exclui os registros na tabela group_post_notification que fazem referência ao post
        DB::table('group_post_notification')
            ->where('post_id', $id)
            ->delete();

        // Exclui os registros na tabela reaction_notification que fazem referência às reações
        DB::table('reaction_notification')
            ->whereIn('reaction_id', function($query) use ($id) {
                $query->select('id')
                      ->from('reaction')
                      ->where('post_id', $id);
            })
            ->delete();

        // Exclui os registros na tabela reaction que fazem referência ao post
        DB::table('reaction')->where('post_id', $id)->delete();

        // Exclui os registros na tabela comment_notification que fazem referência aos comentários
        DB::table('comment_notification')
            ->whereIn('comment_id', function($query) use ($id) {
                $query->select('id')
                      ->from('comment_')
                      ->where('post_id', $id);
            })
            ->delete();

        // Exclui os comentários associados ao post
        DB::table('comment_')->where('post_id', $id)->delete();

        // Exclui os registros na tabela saved_post que fazem referência ao post
        DB::table('saved_post')->where('post_id', $id)->delete();

        // Exclui o post
        if ($post->user_id === auth()->id()) {
            $post->delete();
            return redirect()->route('home')->with('success', 'Post deletado com sucesso.');
        }

        return redirect()->route('home')->with('error', 'Você não tem permissão para deletar este post.');
    }
    
    
    
    


    /**
     * Retrieve posts for the user's timeline.
     */
    public function getPosts()
    {
        $posts = Auth::check()
            ? Auth::user()->visiblePosts()->get()
            : Post::public()->orderBy('post_date', 'desc')->get();

        return response()->json(['posts' => $posts], 200);
    }


    // Modelo Post
public function savedPosts()
{
    return $this->hasMany(SavedPost::class, 'post_id');
}

}
