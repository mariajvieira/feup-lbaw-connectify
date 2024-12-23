@extends('layouts.app')

@section('content')
<div class="edit-post-container">

    <form action="{{ route('post.update', $post->id) }}" method="POST" class="create-post-form" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <h2 class="mb-4">Edit Post</h2>

        <div class="form-group">
            <label for="content">Content:</label>
            <textarea name="content" id="content" rows="5" class="form-control" required>{{ old('content', $post->content) }}</textarea>
        </div>



        <button type="submit" class="btn btn-custom mt-3">Save Changes</button>
    </form> 
</div>
@endsection
