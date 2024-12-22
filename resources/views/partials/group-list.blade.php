
@if($groups->isEmpty())
@else
<h5>Groups</h5>
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