<div class="comment mt-2">
    <div class="d-flex justify-content-between align-items-center">
        <span>
            <strong>
                <a class="text-custom text-decoration-none" href="{{ route('user', ['id' => $comment->user->id]) }}">
                    {{ $comment->user->username }}
                </a>
            </strong>: {{ $comment->comment_content }}
        </span>

        @can('destroy', $comment)
        <form class="delete-comment-form mb-0" action="{{ route('comment.destroy', $comment->id) }}" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm d-flex align-items-center gap-2" onclick="return confirm('Are you sure you want to delete this comment?')">
                <i class="fa-solid fa-trash"></i>
            </button>                  
        </form>
        @endcan
    </div>
</div>
