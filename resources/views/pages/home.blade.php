@extends('layouts.app')

@section('title', 'Home Page')

@section('content')

<div class="text-center mt-5">
    <h1>Posts dos Amigos</h1>
</div>

<div class="container mt-5">
    @foreach($posts as $post)
        <div class="post-item mb-4">
            <h4>{{ $post->user->username }} - {{ \Carbon\Carbon::parse($post->post_date)->format('d/m/Y \à\s H:i') }}</h4>
            <p>{{ $post->content }}</p>
            @if($post->image)
                <img src="{{ asset('storage/' . $post->image) }}" alt="Post Image" class="img-fluid">
            @endif
        </div>
    @endforeach

    @if($posts->isEmpty())
        <p>Você não tem amigos ou seus amigos não postaram ainda.</p>
    @endif
</div>

@endsection



