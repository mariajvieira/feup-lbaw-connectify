@extends('layouts.app')

@section('content')
<div class="edit-post-container">
    <h2>Edit Post</h2>
    <form action="{{ route('post.update', $post->id) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Campo de conteúdo do post -->
        <div class="form-group">
            <label for="content">Content:</label>
            <textarea name="content" id="content" rows="5" class="form-control" required>{{ old('content', $post->content) }}</textarea>
        </div>



        <!-- Campo de visibilidade do post -->
        <div class="form-group">
            <label for="is_public">Public Post:</label>
            <input type="checkbox" name="is_public" id="is_public" value="1" {{ $post->is_public ? 'checked' : '' }} />
        </div>

        <!-- Botão para salvar alterações -->
        <button type="submit" class="btn btn-primary">Save Changes</button>
    </form> 
</div>
@endsection
