@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Saved Posts</h2>

   @if($posts->isEmpty())
        <p>No post saved yet.</p>
    @else
        <div class="post-list">
            @include('pages.posts', ['posts' => $posts]) 
        </div>
    @endif
</div>
@endsection
