<h5>Groups</h5>
@if($groups->isEmpty())
    <p>No groups found.</p>
@else
    @foreach ($groups as $group)
        <div class="group-item mb-1">
            <button 
                class="btn text-custom fw-bold" 
                onclick="window.location.href='{{ route('group.show', $group->id) }}'">
                {{ $group->group_name }}
            </button>
        </div>
    @endforeach
@endif