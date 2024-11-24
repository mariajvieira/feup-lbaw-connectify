@extends('layouts.app')

@section('content')
<div class="edit-post-container">

    <form action="{{ route('post.update', $post->id) }}" method="POST" class="create-post-form">
        @csrf
        @method('PUT')
        <h2>Edit Post</h2>
        <!-- Campo de conteúdo do post -->
        <div class="form-group">
            <label for="content">Content:</label>
            <textarea name="content" id="content" rows="5" class="form-control" required>{{ old('content', $post->content) }}</textarea>
        </div>


        <div class="form-group">
        <label for="is_public" class="field-label">Is Public:</label>
        <select name="is_public" id="is_public" class="form-control">
            <option value="1">Yes</option>
            <option value="0">No</option>
        </select>
    </div>

        <!-- Botão para salvar alterações -->
        <button type="submit" class="btn btn-primary">Save Changes</button>
    </form> 
</div>
@endsection
