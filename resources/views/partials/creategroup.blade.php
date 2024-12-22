@extends('layouts.app')

@section('content')
<div class="container">
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="create-group-container">
        <h2>Create New Group</h2>

        <form action="{{ route('group.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="group_name" class="form-label">Group Name</label>
                <input type="text" name="group_name" id="group_name" value="{{ old('group_name') }}" class="form-control" required>
                @error('group_name') 
                    <div class="text-danger">{{ $message }}</div> 
                @enderror
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea name="description" id="description" rows="4" class="form-control">{{ old('description') }}</textarea>
                @error('description') 
                    <div class="text-danger">{{ $message }}</div> 
                @enderror
            </div>

            <div class="mb-3">
                <label for="is_public" class="form-label">Public</label>
                <select name="is_public" id="is_public" class="form-select" required>
                    <option value="1" {{ old('is_public') == 1 ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ old('is_public') == 0 ? 'selected' : '' }}>No</option>
                </select>
                @error('is_public') 
                    <div class="text-danger">{{ $message }}</div> 
                @enderror
            </div>

            <button type="submit" class="btn btn-custom">Create Group</button>
        </form>
    </div>
</div>
@endsection
