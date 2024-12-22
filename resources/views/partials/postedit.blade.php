@extends('layouts.app')

@section('content')
<div class="edit-post-container">

    <form action="{{ route('post.update', $post->id) }}" method="POST" class="create-post-form" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <h2 class="mb-4">Edit Post</h2>

        <!-- Campo de conteúdo do post -->
        <div class="form-group">
            <label for="content">Content:</label>
            <textarea name="content" id="content" rows="5" class="form-control" required>{{ old('content', $post->content) }}</textarea>
        </div>

        @if($post->images && $post->images->count() > 0)
            <div class="form-group">
                <label>Current Images:</label>
                <div class="post-images">
                    @foreach ($post->images as $image)
                        <div class="image-container mb-2">
                            <img src="{{ asset($image->path) }}" alt="Post Image" class="img-fluid" style="max-width: 200px;">
                            
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="delete_images[]" value="{{ $image->id }}" id="delete-image-{{ $image->id }}">
                                <label class="form-check-label" for="delete-image-{{ $image->id }}">Delete</label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Campo de upload de novas imagens -->
        <div class="form-group">
            <label for="new_images">Upload New Images:</label>
            <input type="file" name="new_images[]" id="new_images" class="form-control-file" multiple>
        </div>

        <!-- Botão para salvar alterações -->
        <button type="submit" class="btn btn-custom mt-3">Save Changes</button>
    </form> 
</div>
@endsection
