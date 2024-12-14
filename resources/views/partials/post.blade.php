<div class="post-item">
    <div class="post-header d-flex justify-content-between align-items-center">
        <div class="user-info">
            <!-- Tornando o nome de usuário clicável e direcionando para o perfil do usuário -->
            <h5>
                <strong>
                    <a href="{{ route('user', ['id' => $post->user->id]) }}">@ {{ $post->user->username }}</a>
                </strong>
            </h5>
        </div>

        @can('edit', $post)
        <!-- Botões Editar e Deletar lado a lado -->
        <div class="post-actions d-flex align-items-center gap-2">
            <a class="btn btn-primary" href="{{ route('post.edit', $post->id) }}">Edit</a>
            <form action="{{ route('post.delete', $post->id) }}" method="POST" style="display: inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this post?')">Delete</button>
            </form>
        </div>
        @endcan
    </div>

    <!-- Conteúdo do post -->
    <p class="post-content">{{ $post->content }}</p>

    <!-- Renderizar imagens dinamicamente -->
    <div class="post-images mt-3">
        @foreach (['image1', 'image2', 'image3'] as $imageField)
            @if (!empty($post->$imageField))
                <div class="post-image mb-2">
                    <img src="{{ asset($post->$imageField) }}" class="img-fluid" alt="Post Image">
                </div>
            @endif
        @endforeach
    </div>
    
    <span class="post-date">Published at: {{ \Carbon\Carbon::parse($post->post_date)->format('d/m/Y \a\t H:i') }}</span>
    
    @php
        // Verificando se o usuário já reagiu ao post
        $userReaction = $post->reactions()
            ->where('user_id', auth()->id())
            ->first();
    @endphp

    <div class="reactions mt-3">
        @foreach (['like', 'laugh', 'cry', 'applause', 'shocked'] as $reaction)
        <button 
            class="reaction-button {{ $userReaction && $userReaction->reaction_type === $reaction ? 'selected' : '' }}" 
            data-reaction-type="{{ $reaction }}" 
            data-post-id="{{ $post->id }}"
            data-reaction-id="{{ $userReaction && $userReaction->reaction_type === $reaction ? $userReaction->id : '' }}">
            {{ ucfirst($reaction) }}
        </button>
        @endforeach
    </div>

    <!-- Exibir comentários já existentes -->
    <div class="comment-section mt-4">
        @foreach ($post->comments as $comment)
        <div class="comment mt-2">
            <p><strong>{{ $comment->user->username }}</strong>: {{ $comment->comment_content }}</p>

            <!-- Botão para excluir comentário -->
            @if ($comment->user_id === auth()->id())
            <form action="{{ route('comment.destroy', $comment->id) }}" method="POST" style="display: inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">Delete Comment</button>
            </form>
            @endif
        </div>
        @endforeach

        <!-- Formulário de adicionar comentário -->
        <form action="{{ route('comment.store', $post->id) }}" method="POST" class="add-comment-form">
            @csrf
            <div class="form-group">
                <label for="comment">Add a Comment:</label>
                <textarea id="comment" name="comment" class="form-control" rows="3" required></textarea>
            </div>
            <button type="submit" class="btn btn-success mt-2">Post Comment</button>
        </form>
    </div>
</div>

{{-- Exemplo de botão para salvar post --}}
<form action="{{ route('posts.save', $post->id) }}" method="POST">
    @csrf
    <button type="submit" class="btn btn-primary">Salvar Post</button>
</form>

{{-- Exemplo de botão para remover post salvo --}}
<form action="{{ route('posts.unsave', $post->id) }}" method="POST">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-danger">Remover dos Salvos</button>
</form>
