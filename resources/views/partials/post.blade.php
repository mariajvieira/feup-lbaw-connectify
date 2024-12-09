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
        $userReaction = $post->reactions
            ->where('user_id', auth()->id())
            ->first();
    @endphp

    <div class="reactions mt-3">
        @foreach (['like', 'laugh', 'cry', 'applause', 'shocked'] as $reaction)
        <button 
            class="reaction-button {{ isset($userReaction) && $userReaction->reaction_type === $reaction ? 'selected' : '' }}" 
            data-reaction-type="{{ $reaction }}" 
            data-post-id="{{ $post->id }}"
            @if(isset($userReaction) && $userReaction->reaction_type === $reaction)
                data-reaction-id="{{ $userReaction->id }}"
            @endif>
            {{ ucfirst($reaction) }}
        </button>

        @endforeach
    </div>
</div>
