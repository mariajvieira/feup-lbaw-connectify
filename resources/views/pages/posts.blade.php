@if($posts->isNotEmpty())
    @foreach($posts as $post)
        @include('partials.post', ['post' => $post]) 
    @endforeach
@else
    <p>No posts available.</p>
@endif
