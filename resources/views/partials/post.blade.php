<div class="post-item">
    <div class="post-header d-flex justify-content-between align-items-center">
        <div class="user-info">
            <!-- Tornando o nome de usuário clicável e direcionando para o perfil do usuário -->
            <h5><strong><a href="{{ route('user', ['id' => $post->user->id]) }}">@ {{ $post->user->username }}</a></strong></h5>
        </div>

        @if($post->user_id === auth()->id())
        <!-- Botão dropdown com Editar e Deletar Post -->
        <div class="dropdown">
            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                ...
            </button>
            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                <li><a class="dropdown-item btn btn-primary" href="{{ route('post.edit', $post->id) }}">Edit Post</a></li>
                <li>
                    <form action="{{ route('post.delete', $post->id) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this post?')">Delete Post</button>
                    </form>
                </li>
            </ul>
        </div>
        @endif
    </div>

    <p class="post-content">{{ $post->content }}</p>
    <span class="post-date">Published at: {{ \Carbon\Carbon::parse($post->post_date)->format('d/m/Y \a\t H:i') }}</span>

    @if($post->image)
        <div class="post-image mt-2">
            <img src="{{ asset('storage/' . $post->image) }}" class="img-fluid" alt="Post Image">
        </div>
    @endif
</div>
