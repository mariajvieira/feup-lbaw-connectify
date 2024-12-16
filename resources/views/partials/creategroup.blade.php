@extends('layouts.app')

@section('content')
<div class="create-group-container">
    <h2>Create New Group</h2>

    <form action="{{ route('group.store') }}" method="POST">
        @csrf

        <label for="group_name">Group Name</label>
        <input type="text" name="group_name" id="group_name" value="{{ old('group_name') }}" required>
        @error('group_name') 
            <div class="error">{{ $message }}</div> 
        @enderror

        <label for="description">Description</label>
        <textarea name="description" id="description" rows="4">{{ old('description') }}</textarea>
        @error('description') 
            <div class="error">{{ $message }}</div> 
        @enderror

        <label for="visibility">Visibility</label>
        <select name="visibility" id="visibility" required>
            <option value="1" {{ old('visibility') == 1 ? 'selected' : '' }}>Visible</option>
            <option value="0" {{ old('visibility') == 0 ? 'selected' : '' }}>Not Visible</option>
        </select>
        @error('visibility') 
            <div class="error">{{ $message }}</div> 
        @enderror

        <label for="is_public">Public</label>
        <select name="is_public" id="is_public" required>
            <option value="1" {{ old('is_public') == 1 ? 'selected' : '' }}>Yes</option>
            <option value="0" {{ old('is_public') == 0 ? 'selected' : '' }}>No</option>
        </select>
        @error('is_public') 
            <div class="error">{{ $message }}</div> 
        @enderror

        <button type="submit">Create Group</button>
    </form>
</div>
@endsection
