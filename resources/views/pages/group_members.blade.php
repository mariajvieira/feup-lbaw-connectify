@extends('layouts.app')

@section('content')
<div class="group-members-container">
    <h1>{{ $group->group_name }} - Members</h1>

    <h3>Owner:</h3>
    <p>{{ $group->owner->name }} ({{ $group->owner->email }})</p>

    <h3>Other Members:</h3>
    <ul>
        @foreach($members as $member)
            @if($member->id !== $group->owner_id)
                <li>{{ $member->name }} ({{ $member->email }})</li>
            @endif
        @endforeach
    </ul>
</div>
@endsection
