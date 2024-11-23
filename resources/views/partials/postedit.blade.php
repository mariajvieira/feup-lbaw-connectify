@extends('layouts.app')

@section('content')
<div class="edit-post-container">
    <h2>Editar Post</h2>
    <form action="{{ route('post.update', $post->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="content">Content</label>
            <textarea name="content" id="content" rows="5" class="form-control" required>{{ old('content', $post->content) }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">Save Changes</button>
    </form> 


</div>
@endsection
