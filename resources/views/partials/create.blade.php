@extends('layouts.app')

@section('content')
<form action="{{ route('post.store') }}" method="POST" enctype="multipart/form-data" class="create-post-form">
    @csrf
    <div class="form-group">
        <label for="content" class="field-label">Post Content:</label>
        <textarea name="content" id="content" class="form-control" rows="4" placeholder="What's on your mind?"></textarea>
    </div>
    <div class="form-group">
        <label for="image" class="field-label">Upload Image (Optional):</label>
        <input type="file" name="image" id="image" class="form-control">
    </div>
    <div class="form-group">
        <label for="is_public" class="field-label">Is Public:</label>
        <select name="is_public" id="is_public" class="form-control">
            <option value="1">Yes</option>
            <option value="0">No</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Create Post</button>
</form>
@endsection
