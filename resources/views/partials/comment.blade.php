<!DOCTYPE html>
<html lang="pt-br">
<!-- @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif -->

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comentário com Edição e Exclusão</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="container mt-5">
        <div class="comment mt-2" id="comment-{{ $comment->id }}">
            <div class="d-flex justify-content-between align-items-center">
                <span>
                    <strong>
                        <a class="text-custom text-decoration-none" href="{{ route('user', ['id' => $comment->user->id]) }}">
                            {{ $comment->user->username }}
                        </a>
                    </strong>: <span class="comment-text">{{ $comment->comment_content }}</span>
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

    <script>
        $(document).on('click', '.delete-comment', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete comment!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    var form = $(this).closest('form');
                    $.ajax({
                        url: form.attr('action'),
                        type: form.attr('method'),
                        data: form.serialize(),
                        success: function(response) {
                            // Remover o comentário do DOM
                            form.closest('.comment').remove();
                            Swal.fire('The comment has been excluded.', 'Success');
                        },
                        error: function(xhr, status, error) {
                            Swal.fire('Error', 'Error excluding comment.', 'error');
                        }
                    });
                }
            });
        });

        // Função para editar um comentário
        $(document).on('click', '.edit-comment', function() {
            var commentId = $(this).data('comment-id');
            console.log(commentId);
            var commentContent = $(this).closest('.comment').find('.comment-text').text().trim();
            
            //Edit and save
            var editField = `<input type="text" class="form-control edit-comment-field" value="${commentContent}" />`;
            $(this).closest('.comment').find('.comment-text').html(editField);

            $(this).html('<i class="fa-solid fa-save"></i>').removeClass('edit-comment').addClass('save-comment');
        });

        // Função para salvar a edição do comentário
        $(document).on('click', '.save-comment', function() {
            var button = $(this);
            var commentId = $(this).data('comment-id'); 
            var newContent = $(this).closest('.comment').find('.edit-comment-field').val();
            console.log(newContent);
            $.ajax({
                url: '/comments/' + commentId + '/edit',  // A rota para atualizar o comentário
                type: 'PUT',

                data: {
                    _token: '{{ csrf_token() }}',
                    content: newContent
                },
                success: function(response) {
                    // Atualizar o comentário com o novo texto
                    button.closest('.comment').find('.comment-text').html(newContent);
                    button.html('<i class="fa-solid fa-pen"></i>').removeClass('save-comment').addClass('edit-comment');
                    console.log('Comentário atualizado com sucesso');
                },
                error: function(xhr, status, error) {
                    alert('Erro ao salvar o comentário');
                }
            });
        });
    </script>
</body>
</html>
