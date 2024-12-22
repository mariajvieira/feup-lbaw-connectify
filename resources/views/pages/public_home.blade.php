@extends('layouts.app')

@section('content')
<div class="container">
<h1 class="display-4 text-center mb-4">Welcome to Connectify!</h1>
<p class="lead text-center mb-5">Where connections spark ideas, and every voice matters. Share yours today!</p>


    @if($posts->isEmpty())
        <div class="alert alert-info text-center">
            <strong>No public posts available.</strong> Be the first to share!
        </div>
    @else
        <div class="post-list">
            @include('pages.posts', ['posts' => $posts]) 
        </div>
    @endif
</div>
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@endsection
