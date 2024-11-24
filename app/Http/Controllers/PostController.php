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
            'image1' => 'nullable|image|max:2048',
            'image2' => 'nullable|image|max:2048',
            'image3' => 'nullable|image|max:2048',
            'is_public' => 'required|boolean',
        ]);

        // Criar o post
        $post = new Post();
        $post->user_id = auth()->id();
        $post->content = $validated['content'];
        $post->is_public = $validated['is_public'];

        // Upload de imagens
        foreach (['image1', 'image2', 'image3'] as $imageField) {
            if ($request->hasFile($imageField)) {
                $post->$imageField = $request->file($imageField)->store('posts', 'public');
            }
        }

        $post->save();

        return redirect()->route('home')->with('success', 'Post criado com sucesso!');
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
            ? Auth::user()->visiblePosts()->get()
            : Post::public()->orderBy('post_date', 'desc')->get();

        return response()->json(['posts' => $posts], 200);
    }
}
