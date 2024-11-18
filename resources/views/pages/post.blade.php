@extends('layouts.app')

@section('title', $post->content)

@section('content')
    <section id="cards">
        @include('partials.post', ['post' => $card])
    </section>
@endsection