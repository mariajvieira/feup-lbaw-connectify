<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\SavedPost;
use App\Models\Comment;
use App\Models\User;

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
            'content' => 'nullable|string|max:255', 
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

        // Processamento das marcações de usuário
        if (!empty($request->content)) {
            preg_match_all('/@(\w+)/', $request->content, $matches);

            if (!empty($matches[1])) {
                $usernames = $matches[1]; 
                $taggedUsers = User::whereIn('username', $usernames)->get();

                foreach ($taggedUsers as $user) {
                    $post->taggedUsers()->syncWithoutDetaching([
                        $user->id => ['tagged_by' => Auth::id(), 'created_at' => now()],
                    ]);
                }
            }
        }

        if (empty($request->content) && empty($request->image1) && empty($request->image2) && empty($request->image3)) {
            return redirect()->route('home')->with('error', 'The post must contain content or an image.');
        }

        // Processamento das imagens
        for ($i = 1; $i <= 3; $i++) {
            if ($request->hasFile('image'.$i)) {
                $image = $request->file('image'.$i);

                // Gerar nome do arquivo com base no post ID e número da imagem
                $imageName = $post->id . '.' . $i . '.' . 'jpg';

                $imagePath = $image->storeAs('images/posts', $imageName, 'local'); 
                $post->{'image'.$i} = 'posts/' . $imageName;
            }
        }

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
    
        // Atualizando o conteúdo e a visibilidade
        $post->update([
            'content' => $validated['content'] ?? $post->content,
            'is_public' => $validated['is_public'],
        ]);
    
        // Atualizando as imagens
        foreach (['image1', 'image2', 'image3'] as $imageField) {
            if ($request->hasFile($imageField)) {
                // Armazena a imagem no storage/app/public/posts
                $post->$imageField = $request->file($imageField)->store('images/posts', 'local');
            }
        }
    
        // Salvando as alterações no post
        $post->save();
    
        return redirect()->route('home')->with('success', 'Post atualizado com sucesso!');
    }

    public function getPostImage($postId, $imageNumber)
    {
        $post = Post::findOrFail($postId);
        
        // Construa o campo da imagem dinamicamente com base no número da imagem
        $imageField = 'image' . $imageNumber;
        
        // Verifique se o campo de imagem é nulo
        if (!$post->$imageField) {
            // Retorna uma resposta vazia, com status 204 (No Content)
            return response()->noContent();
        }
    
        // Caso a imagem não seja nula, continue com o caminho do arquivo
        $imagePath = 'images/' . $post->$imageField; 
        
        // Retorna a imagem armazenada
        return response()->file(storage_path('app/' . $imagePath));
    }
    
    
    

    public function delete($id)
    {
        $post = Post::find($id);
    
        if (!$post) {
            return redirect()->route('home')->with('error', 'Post não encontrado.');
        }
    
        for ($i = 1; $i <= 3; $i++) {
            $imageField = 'image' . $i;
            if ($post->$imageField && Storage::exists('images/posts/' . $post->$imageField)) {
                Storage::delete('images/posts/' . $post->$imageField);  
            }
        }
    
        DB::table('group_post_notification')->where('post_id', $id)->delete();
        DB::table('reaction_notification')
            ->whereIn('reaction_id', function ($query) use ($id) {
                $query->select('id')->from('reaction')->where('target_id', $id);
            })->delete();
        DB::table('reaction')->where('target_id', $id)->delete();
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
    
    public function showSavedPosts()
    {
        // Obter o usuário autenticado
        $user = Auth::user();
    
        // Carregar os posts salvos relacionados ao usuário
        $savedPosts = $user->savedPosts()->with('user')->get();
    
        // Retornar a view com os posts
        return view('pages.savedPosts', ['posts' => $savedPosts]);

    }


    /**
 * Retrieve posts where the authenticated user is tagged.
 */
    // public function getTaggedPosts()
    // {
    //     $userId = Auth::id();

    //     $taggedPosts = Post::whereHas('tags', function ($query) use ($userId) {
    //         $query->where('user_id', $userId);
    //     })->with('user', 'tags')->orderBy('created_at', 'desc')->get();

    //     return response()->json(['posts' => $taggedPosts], 200);
    // }


    
    public function showTaggedPosts()
    {
        $userId = Auth::id();
    
        // Buscar posts onde o usuário foi marcado
        $taggedPosts = Post::whereHas('taggedUsers', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->with(['user', 'taggedUsers'])->get();
    
        return view('pages.tagged', ['posts' => $taggedPosts]);
    }
    

    
    public function getPostReactionCount($postId)
    {
        $post = Post::findOrFail($postId);
        
        $reactionCount = $post->reactions()->count();
    
        // Retorna a contagem de reações como JSON
        return response()->json([
            'reactionCount' => $reactionCount
        ]);
    }
    
    public function showReactions($postId)
    {
        $post = Post::find($postId);
        
        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }
    
        // Carregue as reações relacionadas ao post
        $reactions = $post->reactions()->with('user')->get();
    
        // Get reaction icon for each reaction type
        foreach ($reactions as $reaction) {
            $reaction->icon = $this->getReactionIcon($reaction->reaction_type);
        }
    
        return response()->json([
            'reactions' => $reactions
        ]);
    }
    
    public function showReactionsPage($postId)
    {
        $post = Post::findOrFail($postId);
    
        // Carregar as reações com o usuário associado
        $reactions = $post->reactions()->with('user')->get();
    
        // Add reaction icons to each reaction
        foreach ($reactions as $reaction) {
            $reaction->icon = $this->getReactionIcon($reaction->reaction_type);
        }
    
        return view('pages.reactions', compact('post', 'reactions'));
    }
    

    function getReactionIcon($type)
    {
        $icons = [
            'like' => 'fa-heart',
            'laugh' => 'fa-face-laugh-squint',
            'cry' => 'fa-face-sad-cry',
            'applause' => 'fa-hands-clapping',
            'shocked' => 'fa-face-surprise'
        ];

        return $icons[$type] ?? 'fa-smile';
    }



}
