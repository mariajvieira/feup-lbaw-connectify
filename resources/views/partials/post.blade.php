
<div class="post-item">
    <div class="post-header d-flex justify-content-between align-items-center">
        <div class="user-info">
            <!-- Tornando o nome de usuário clicável e direcionando para o perfil do usuário -->
            <h5>
                <strong>
                    <a 
                        href=" {{ route('user', ['id' => $post->user->id]) }}">
                        @ {{ $post->user->username }}
                    </a>
                </strong>
            </h5>
        </div>

        @can('edit', $post)
        <!-- Botões Editar e Deletar lado a lado -->
        <div class="post-actions d-flex align-items-center gap-2">
            <a class="btn btn-primary" href="{{ route('post.edit', $post->id) }}"><i class="fa-solid fa-pen"></i></a>
            <form action="{{ route('post.delete', $post->id) }}" method="POST" style="display: inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger d-flex align-items-center gap-2" onclick="return confirm('Are you sure you want to delete this post?')">
                    <i class="fa-solid fa-trash"></i>
                </button>            
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
        @foreach ([
            'like' => 'fa-regular fa-heart',
            'laugh' => 'fa-regular fa-face-laugh-squint',
            'cry' => 'fa-regular fa-face-sad-cry',
            'applause' => 'fa-solid fa-hands-clapping', 
            'shocked' => 'fa-regular fa-face-surprise'
            ] as $reaction => $icon)
            <button 
                class="reaction-button {{ $userReaction && $userReaction->reaction_type === $reaction ? 'selected' : '' }}" 
                data-reaction-type="{{ $reaction }}" 
                data-post-id="{{ $post->id }}"
                @if (!auth()->check())
                    onclick="alert('You need to login to react.'); window.location.href='{{ route('login') }}'; return false;"                
                @else
                    data-reaction-id="{{ $userReaction && $userReaction->reaction_type === $reaction ? $userReaction->id : '' }}"
                @endif
            >
                <i class="{{ $icon }}"></i> 
                <span class="reaction-count">{{ $post->reactions->where('reaction_type', $reaction)->count() }}</span>

            </button>
        @endforeach
    </div>


    <span class="reaction-count" id="reaction-count-{{ $post->id }}" data-post-id="{{ $post->id }}">
        <a href="{{ route('post.reactions', $post->id) }}">
            {{ $post->reactions->count() }} 
            {{ $post->reactions->count() === 1 ? 'reaction' : 'reactions' }}
        </a>
    </span>

    <!-- Exibir comentários já existentes -->
    <div class="comment-section mt-4">
        @foreach ($post->comments as $comment)
            <div class="comment mt-2">
            <p>
                <strong>
                    <a href="{{ route('user', ['id' => $comment->user->id]) }}">
                        {{ $comment->user->username }}
                    </a>
                </strong>: {{ $comment->comment_content }}
            </p>

            <!-- Botão para excluir comentário -->



        @can('destroy', $comment)
            <form class="delete-comment-form" action="{{ route('comment.destroy', $comment->id) }}" method="POST" style="display: inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger d-flex align-items-center gap-2" onclick="return confirm('Are you sure you want to delete this comment?')">
                    <i class="fa-solid fa-trash"></i>
                </button>                  
            </form>
        @endcan

            </div>
        @endforeach

        <!-- Formulário de adicionar comentário -->
        @if (auth()->check())
            <form action="{{ route('comment.store', $post->id) }}" method="POST" class="add-comment-form" data-post-id="{{ $post->id }}">
                @csrf
                <div class="form-group">
                    <label for="comment">Add a Comment:</label>
                    <textarea id="comment" name="comment" class="form-control" rows="3" required></textarea>
                </div>
                <button type="submit" class="btn btn-success mt-2">Post Comment</button>
            </form>
        @else
            <div class="alert alert-info">
                <a href="{{ route('login') }}">Login</a> to add a comment.
            </div>
        @endif

    </div>


    @if (auth()->check())
    <button class="saveButton btn btn-light" data-post-id="{{ $post->id }}">
        <i class="fa {{ $post->isSavedByUser() ? 'fa-bookmark' : 'fa-bookmark-o' }}"></i>
        {{ $post->isSavedByUser() ? 'Saved' : 'Save' }}
    </button>
@endif


</div>



