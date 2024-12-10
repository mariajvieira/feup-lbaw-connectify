<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\SavedPost;
use App\Models\Comment;

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
        // Validação dos dados
        $request->validate([
            'content' => 'nullable|string|max:255', // A descrição é opcional, mas deve ser uma string se fornecida
            'is_public' => 'required|boolean',
            'image1' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'image2' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'image3' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);
        
        // Criação do post
        $post = new Post();
        $post->content = $request->content;
        $post->is_public = $request->is_public;
        $post->user_id = Auth::id(); 
        
        // Salvar o post inicialmente para obter o post ID
        $post->save();
        
        // Processamento das imagens e armazenamento nos campos image1, image2, image3
        for ($i = 1; $i <= 3; $i++) {
            if ($request->hasFile('image'.$i)) {
                $image = $request->file('image'.$i);
                
                // Criar o caminho da imagem diretamente dentro de public/images/
                $imageDirectory = public_path('images');
                
                // Armazenar a imagem na pasta com o nome baseado no post_id e número da imagem
                $imagePath = $image->move($imageDirectory, $post->id . '.' . $i . '.' . $image->getClientOriginalExtension());
                
                // Salva o caminho relativo da imagem no banco de dados
                $post->{'image'.$i} = 'images/' . basename($imagePath);
            }
        }
        
        // Atualizar o post após o processamento das imagens
        $post->save();
        
        return redirect()->route('home')->with('success', 'Post criado com sucesso');
    }
    
    


    public function edit($id)
    {
        $post = Post::find($id);

        if (!$post) {
            return redirect()->route('home')->with('error', 'Post não encontrado.');
        }

        return view('partials.postedit', compact('post'));
    }

    /**
     * Update a specific post.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'content' => 'nullable|string',
            'is_public' => 'required|boolean',
            'image1' => 'nullable|image|max:2048',
            'image2' => 'nullable|image|max:2048',
            'image3' => 'nullable|image|max:2048',
        ]);

        $post = Post::findOrFail($id);

        $post->update([
            'content' => $validated['content'] ?? $post->content,
            'is_public' => $validated['is_public'],
        ]);

        // Atualizar imagens
        foreach (['image1', 'image2', 'image3'] as $imageField) {
            if ($request->hasFile($imageField)) {
                $post->$imageField = $request->file($imageField)->store('posts', 'public');
            }
        }

        $post->save();

        return redirect()->route('home')->with('success', 'Post atualizado com sucesso!');
    }

    /**
     * Delete a post.
     */
    public function delete($id)
    {
        $post = Post::find($id);

        if (!$post) {
            return redirect()->route('home')->with('error', 'Post não encontrado.');
        }

        // Excluir dependências
        DB::table('group_post_notification')->where('post_id', $id)->delete();
        DB::table('reaction_notification')
            ->whereIn('reaction_id', function ($query) use ($id) {
                $query->select('id')->from('reaction')->where('post_id', $id);
            })->delete();
        DB::table('reaction')->where('post_id', $id)->delete();
        DB::table('comment_notification')
            ->whereIn('comment_id', function ($query) use ($id) {
                $query->select('id')->from('comment_')->where('post_id', $id);
            })->delete();
        DB::table('comment_')->where('post_id', $id)->delete();
        DB::table('saved_post')->where('post_id', $id)->delete();

        $post->delete();

        return redirect()->route('home')->with('success', 'Post deletado com sucesso!');
    }

    /**
     * Retrieve posts for the user's timeline.
     */
    public function getPosts()
    {
        $posts = Auth::check()
            ? Auth::user()->visiblePosts()->with('reactions')->orderBy('post_date', 'desc')->get()
            : Post::public()->with('reactions')->orderBy('post_date', 'desc')->get();
    
        return response()->json(['posts' => $posts], 200);
    }
    

    public function save($id)
    {
        $postExists = SavedPost::where('user_id', Auth::id())->where('post_id', $id)->exists();
    
        if ($postExists) {
            return redirect()->back()->with('error', 'O post já está salvo.');
        }
    
        SavedPost::create([
            'user_id' => Auth::id(),
            'post_id' => $id,
        ]);
    
        return redirect()->back()->with('success', 'Post salvo com sucesso!');
    }
    
    public function unsave($id)
    {
        $savedPost = SavedPost::where('user_id', Auth::id())->where('post_id', $id)->first();
    
        if (!$savedPost) {
            return redirect()->back()->with('error', 'O post não está salvo.');
        }
    
        $savedPost->delete();
    
        return redirect()->back()->with('success', 'Post removido dos salvos com sucesso!');
    }
    


}
