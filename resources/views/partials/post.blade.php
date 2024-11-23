<div class="post-item">
    <div class="post-header d-flex justify-content-between align-items-center">
        <h4>{{ $post->title }}</h4>

        <!-- Dropdown com 3 pontinhos apenas para os próprios posts -->
        @if($post->user_id === auth()->id())
        <div class="dropdown">
            <!-- Botão com os 3 pontos -->
            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                ...
            </button>
            
            <!-- Menu do dropdown -->
            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                <li><a class="dropdown-item" href="{{ route('post.edit', $post->id) }}">Edit Post</a></li>
                <li>
                    <form action="{{ route('post.delete', $post->id) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="dropdown-item" onclick="return confirm('Are you sure you want to delete this post?')">Delete Post</button>
                    </form>
                </li>
            </ul>
        </div>

        @endif
    </div>

    <p>{{ $post->content }}</p>
    <span class="post-date">Posted on: {{ \Carbon\Carbon::parse($post->post_date)->format('d/m/Y \à\s H:i') }}</span>

    @if($post->image)
        <div class="post-image mt-2">
            <img src="{{ asset('storage/' . $post->image) }}" class="img-fluid" alt="Post Image">
        </div>
    @endif
</div>
