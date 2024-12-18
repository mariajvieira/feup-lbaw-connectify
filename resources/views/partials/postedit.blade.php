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

        @if($post->images)
            <div class="form-group">
                <label>Current Images:</label>
                <div class="post-images">
                    @foreach ($post->images as $image)
                        <div class="image-container">
                            <img src="{{ asset($image->path) }}" alt="Post Image" class="img-fluid mb-2">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="delete_images[]" value="{{ $image->id }}" id="delete-image-{{ $image->id }}">
                                <label class="form-check-label" for="delete-image-{{ $image->id }}">Delete</label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Botão para salvar alterações -->
        <button type="submit" class="btn btn-primary">Save Changes</button>
    </form> 
</div>
@endsection
