@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <!-- Coluna para os grupos -->
        @include('partials.group-list', ['groups' => $groups])

        <!-- Coluna para os posts -->
        <div class="col-md-9">
            @if($posts->isEmpty())
                <p>No posts to show.</p>
            @else
                <div class="post-list">
                    @include('pages.posts', ['posts' => $posts]) 
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
