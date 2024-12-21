@if($posts->isNotEmpty())
    @foreach($posts as $post)
        <div class="mb-4">
            @include('partials.post', ['post' => $post]) 
        </div>
    @endforeach
@else
    <p>No posts available.</p>
@endif