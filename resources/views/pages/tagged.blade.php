@extends('layouts.app')

@section('content')
    <h1>Tagged Posts</h1>

    @if($posts->isEmpty())
        <p>You haven't been tagged yet</p>
    @else
        <strong>Marcados:</strong> 
            <div class="post-list">
                @include('pages.posts', ['posts' => $posts]) 
            </div>
    @endif
@endsection
