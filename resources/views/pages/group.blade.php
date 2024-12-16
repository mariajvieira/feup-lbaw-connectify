@extends('layouts.app')

@section('content')
<div class="group-details-container">
    <h1>{{ $group->group_name }}</h1>
    <p><strong>Description:</strong> {{ $group->description }}</p>
    <p><strong>Visibility:</strong> {{ $group->visibility ? 'Visible' : 'Not Visible' }}</p>
    <p><strong>Public:</strong> {{ $group->is_public ? 'Yes' : 'No' }}</p>
</div>
@endsection