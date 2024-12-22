@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Create New Post</h2>
    
    <!-- Display error messages -->
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('post.store') }}" method="POST" enctype="multipart/form-data" class="create-post-form">
        @csrf
        
        <div class="form-group mb-3">
            <label for="content" class="field-label">Post Content:</label>
            <textarea name="content" id="content" class="form-control" rows="4" placeholder="What's on your mind?">{{ old('content') }}</textarea>
            @error('content')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="form-group mb-3">
            <label for="image1" class="field-label">Upload Image 1 (Optional):</label>
            <input type="file" name="image1" id="image1" class="form-control">
            @error('image1')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="form-group mb-3">
            <label for="image2" class="field-label">Upload Image 2 (Optional):</label>
            <input type="file" name="image2" id="image2" class="form-control">
            @error('image2')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="form-group mb-3">
            <label for="image3" class="field-label">Upload Image 3 (Optional):</label>
            <input type="file" name="image3" id="image3" class="form-control">
            @error('image3')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="form-group mb-3">
            <label for="is_public" class="field-label">Is Public:</label>
            <select name="is_public" id="is_public" class="form-select">
                <option value="1" {{ old('is_public') == 1 ? 'selected' : '' }}>Yes</option>
                <option value="0" {{ old('is_public') == 0 ? 'selected' : '' }}>No</option>
            </select>
            @error('is_public')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-custom">Create Post</button>
    </form>
</div>
@endsection
