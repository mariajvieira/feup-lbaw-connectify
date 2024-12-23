<div class="container mt-5">
    <div class="comments-list mt-2" id="comment-{{ $comment->id }}">
        <div class="d-flex justify-content-between align-items-center">
            <span>
                <strong>
                    <a class="text-custom text-decoration-none" href="{{ route('user', ['id' => $comment->user->id]) }}">
                        {{ $comment->user->username }}
                    </a>
                </strong>: 
                <span class="comment-text">{{ $comment->comment_content }}</span>
            </span>




            @php
                $userReaction = $comment->reactions()->where('user_id', auth()->id())->first();
            @endphp
            <div class="comment-reactions d-flex justify-content-end ms-3">
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
                        data-comment-id="{{ $comment->id }}"
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
            <span class="reaction-count" id="reaction-count-{{ $post->id }}" data-comment-id="{{ $comment->id }}">
                <a class="text-custom text-decoration-none" href="{{ route('comment.reactions', $comment->id) }}">
                    {{ $comment->reactions->count() }} 
                    {{ $comment->reactions->count() === 1 ? 'reaction' : 'reactions' }}
                </a>
            </span>
            </div>





            @can('destroy', $comment)
            <div class="post-actions d-flex align-items-center gap-2">
                <a href="javascript:void(0)" class="edit-comment btn btn-custom" data-comment-id="{{ $comment->id }}">
                    <i class="fa-solid fa-pen"></i>
                </a>
                <form class="delete-comment-form mb-0" action="{{ route('comment.destroy', $comment->id) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger d-flex align-items-center gap-2 delete-comment">
                        <i class="fa-solid fa-trash"></i> 
                    </button>
                </form>
            </div>
            @endcan
        </div>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

