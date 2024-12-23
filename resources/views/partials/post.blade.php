<div class="post-item">
    <div class="post-header d-flex justify-content-between align-items-center">
        <div class="user-info">
            <h5>
                <strong>
                    <a href="{{ route('user', ['id' => $post->user->id]) }}" class="text-decoration-none text-custom">
                        @ {{ $post->user->username }}
                    </a>
                </strong>
            </h5>
        </div>

        @can('edit', $post)
        <div class="post-actions d-flex align-items-center gap-2">
            <a class="btn btn-custom" href="{{ route('post.edit', $post->id) }}"><i class="fa-solid fa-pen"></i></a>
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

    <p class="post-content">{{ $post->content }}</p>

    <div class="post-images mt-3">
        <div id="image-slider" class="position-relative">
            <div class="post-image-container d-flex">

                    <div class="post-image" style="flex: 1;">
                        <img src="{{ route('post.image', ['postId' => $post->id, 'imageNumber' => 1]) }}" class="img-fluid" alt="Post Image" style="width: 100%; height: 500px; object-fit: cover;">
                    </div>

              
                    <div class="post-image" style="flex: 1;">
                        <img src="{{ route('post.image', ['postId' => $post->id, 'imageNumber' => 2]) }}" class="img-fluid" alt="Post Image" style="width: 100%; height: 500px; object-fit: cover;">
                    </div>
          

                @if (!empty($post->IMAGE3))
                    <div class="post-image" style="flex: 1;">
                        <img src="{{ route('post.image', ['postId' => $post->id, 'imageNumber' => 3]) }}" class="img-fluid" alt="Post Image" style="width: 100%; height: 500px; object-fit: cover;">
                    </div>
                @endif

            </div>
        </div>
    </div>





    @php
        $userReaction = $post->reactions()->where('user_id', auth()->id())->first();
    @endphp
    <!-- Reactions à direita do post -->
    <div class="post-reactions d-flex justify-content-end ms-3">
        @foreach ([
            'like' => 'fa-regular fa-heart',
            'laugh' => 'fa-regular fa-face-laugh-squint',
            'cry' => 'fa-regular fa-face-sad-cry',
            'applause' => 'fa-solid fa-hands-clapping', 
            'shocked' => 'fa-regular fa-face-surprise'
            ] as $reaction => $icon)
            <button 
                class="reaction-button {{ $userReaction && $userReaction->reaction_type === $reaction ? 'btn-outline-danger' : 'btn-outline-secondary' }} btn"
                data-reaction-type="{{ $reaction }}" 
                data-post-id="{{ $post->id }}"
                @if (!auth()->check())
                    onclick="alert('You need to login to react.'); window.location.href='{{ route('login') }}'; return false;"                
                @else
                    data-reaction-id="{{ $userReaction && $userReaction->reaction_type === $reaction ? $userReaction->id : '' }}"
                @endif
            >
                <i class="{{ $icon }}"></i> 
            </button>
        @endforeach
    </div>

    <div>
    <span class="reaction-count" id="reaction-count-{{ $post->id }}" data-post-id="{{ $post->id }}">
        <a class="text-custom text-decoration-none" href="{{ route('post.reactions', $post->id) }}">
            {{ $post->reactions->count() }} 
            {{ $post->reactions->count() === 1 ? 'reaction' : 'reactions' }}
        </a>
    </span>
    </div>

    <div class="comments-list comment-section mt-4">
        @foreach ($post->comments as $comment)
            @include('partials.comment', ['comment' => $comment])
        @endforeach

        @if (auth()->check())
            <form action="{{ route('comment.store', $post->id) }}" method="POST" class="add-comment-form" data-post-id="{{ $post->id }}">
            @csrf
            <div class="form-group d-flex align-items-center">
                <textarea id="comment" name="comment" class="form-control me-2" rows="1" required placeholder="Add a comment..."></textarea>
                <button type="submit" class="btn btn-custom post-comment">Post</button>
            </div>
            </form>
        @else
            <div class="alert alert-info">
            <a href="{{ route('login') }}">Login</a> to add a comment.
            </div>
        @endif  
    </div>


    <!-- Save -->
    @if (auth()->check())
    <div class="post" data-id="{{ $post->id }}">

        <!-- Botões de salvar ou removido -->
        <button class="save-post-btn btn-custom" 
                data-post-id="{{ $post->id }}"
                data-saved="{{ Auth::user()->savedPosts->contains($post) ? 'true' : 'false' }}">
            <i class="fa{{ Auth::user()->savedPosts->contains($post) ? 's' : 'r' }} fa-bookmark"></i>
            {{ Auth::user()->savedPosts->contains($post) ? 'Saved' : 'Save' }}
        </button>
    </div>
@endif




    <span class="post-date text-muted small">Published at: {{ \Carbon\Carbon::parse($post->post_date)->format('d/m/Y \a\t H:i') }}</span>

</div>
