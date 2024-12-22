
    <div class="container mt-5">
        <div class="comment mt-2" id="comment-{{ $comment->id }}">
            <div class="d-flex justify-content-between align-items-center">
                <span>
                    <strong>
                        <a class="text-custom text-decoration-none" href="{{ route('user', ['id' => $comment->user->id]) }}">
                            {{ $comment->user->username }}
                        </a>
                    </strong>: 
                    <span class="comment-text">{{ $comment->comment_content }}</span>
                </span>

                @can('destroy', $comment)
                <div class="post-actions d-flex align-items-center gap-2">
                    <a href="javascript:void(0)" class="btn btn-custom edit-comment" data-comment-id="{{ $comment->id }}">
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

