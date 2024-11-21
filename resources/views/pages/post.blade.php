@extends('layouts.app')

@section('title', $post->content)

@section('content')
    <section id="posts">
        @include('partials.post', ['post' => $post])
    </section>
@endsection