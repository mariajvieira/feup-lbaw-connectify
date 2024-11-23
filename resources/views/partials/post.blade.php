<div class="post-item">
    <div class="post-header d-flex justify-content-between align-items-center">

        <div class="user-info">
            <!-- Tornando o nome de usuário clicável e direcionando para o perfil do usuário -->
            <h5><strong><a href="{{ route('user', ['id' => $post->user->id]) }}">@ {{ $post->user->username }}</a></strong></h5>
        </div>

        @if($post->user_id === auth()->id())
        <div class="dropdown">
            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                ...
            </button>
            
            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                <li><a class="dropdown-item" href="{{ route('post.edit', $post->id) }}">Editar Post</a></li>
                <li>
                    <form action="{{ route('post.delete', $post->id) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="dropdown-item" onclick="return confirm('Are you sure you want to delete this post?')">Deletar Post</button>
                    </form>
                </li>
            </ul>
        </div>
        @endif
    </div>

    <p>{{ $post->content }}</p>
    <span class="post-date">Publicado em: {{ \Carbon\Carbon::parse($post->post_date)->format('d/m/Y \à\s H:i') }}</span>

    @if($post->image)
        <div class="post-image mt-2">
            <img src="{{ asset('storage/' . $post->image) }}" class="img-fluid" alt="Post Image">
        </div>
    @endif
</div>
