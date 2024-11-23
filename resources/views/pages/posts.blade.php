@if($posts->isNotEmpty())
    @foreach($posts as $post)
        @include('partials.post', ['post' => $post]) <!-- Chama o partial para cada post -->
    @endforeach
@else
    <p>No posts available.</p>
@endif
