@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Create New Post for Group: {{ $group->group_name }}</h2>
    
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('group.post.store', $group->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="group_id" value="{{ $group->id }}">

        <div class="form-group mb-3">
            <label for="content">Post Content:</label>
            <textarea name="content" id="content" class="form-control" rows="4" placeholder="Write your post here">{{ old('content') }}</textarea>
        </div>

        <div class="form-group mb-3">
            <label for="image1">Upload Image 1 (Optional):</label>
            <input type="file" name="image1" id="image1" class="form-control">
        </div>

        <div class="form-group mb-3">
            <label for="image2">Upload Image 2 (Optional):</label>
            <input type="file" name="image2" id="image2" class="form-control">
        </div>

        <div class="form-group mb-3">
            <label for="image3">Upload Image 3 (Optional):</label>
            <input type="file" name="image3" id="image3" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Create Post</button>
    </form>
</div>
@endsection
