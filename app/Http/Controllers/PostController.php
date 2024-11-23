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
    public function create(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:user_,user_id',
            'group_id' => 'nullable|exists:group_,group_id',
            'content' => 'nullable|string',
            'IMAGE1' => 'nullable|string',
            'IMAGE2' => 'nullable|string',
            'IMAGE3' => 'nullable|string',
            'is_public' => 'required|boolean',
        ]);

        $post = new Post();
        $post->user_id = $validated['user_id'];
        $post->group_id = $validated['group_id'] ?? null;
        $post->content = $validated['content'] ?? null;
        $post->IMAGE1 = $validated['IMAGE1'] ?? null;
        $post->IMAGE2 = $validated['IMAGE2'] ?? null;
        $post->IMAGE3 = $validated['IMAGE3'] ?? null;
        $post->is_public = $validated['is_public'];

        $this->authorize('create', $post);

        $post->save();

        return response()->json($post, 201);
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
        // Encontrar o post
        $post = Post::findOrFail($id);
    
        // Verificar se o usuário tem permissão para editar o post
        if ($post->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }
    
        // Validar os dados do formulário
        $validated = $request->validate([
            'content' => 'nullable|string',
            'IMAGE1' => 'nullable|string',
            'IMAGE2' => 'nullable|string',
            'IMAGE3' => 'nullable|string',
            'is_public' => 'required|boolean',
        ]);
    
        // Atualizar os campos do post
        $post->content = $validated['content'] ?? $post->content;
        $post->IMAGE1 = $validated['IMAGE1'] ?? $post->IMAGE1;
        $post->IMAGE2 = $validated['IMAGE2'] ?? $post->IMAGE2;
        $post->IMAGE3 = $validated['IMAGE3'] ?? $post->IMAGE3;
        $post->is_public = $validated['is_public'];
    
        // Salvar o post
        $post->save();
        dd(auth()->id());
    
        // Redirecionar para o perfil do usuário com uma mensagem de sucesso
        return redirect()->route('user', 1)->with('success', 'Post updated successfully!');
    }
    

    public function edit($id)
    {
        $post = Post::findOrFail($id);
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
    
}
