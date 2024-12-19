@extends('layouts.app')

@section('content')
    <h1>Tagged Posts</h1>

    @if($posts->isEmpty())
        <p>You haven't been tagged yet</p>
    @else
        <div class="post-list-container">
            <div class="post-list">
                @include('pages.posts', ['posts' => $posts]) 
            </div>
        </div>
    @endif
@endsection
