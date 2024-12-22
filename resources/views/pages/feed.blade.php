@extends('layouts.app')

@section('content')
<div class="container">

            @if($posts->isEmpty())
                <p>No posts to show.</p>
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
