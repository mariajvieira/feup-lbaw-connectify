@if($posts->isNotEmpty())
    @foreach($posts as $post)
        @can('view', $post) 
            <div class="mb-4">
                @include('partials.post', ['post' => $post]) 
            </div>
        @else
            <p>You do not have permission to view this post.</p>
        @endcan
    @endforeach
@else
    <p>No posts available.</p>
@endif
