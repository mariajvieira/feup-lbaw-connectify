<div class="col-md-3">
    <h5>Groups</h5>
    @if($groups->isEmpty())
        <p>No groups found.</p>
    @else
        @foreach ($groups as $group)
            <div class="group-item mb-3">
                <button 
                    class="btn btn-primary" 
                    onclick="window.location.href='{{ route('group.show', $group->id) }}'">
                    {{ $group->group_name }}
                </button>
            </div>
        @endforeach
    @endif
</div>
