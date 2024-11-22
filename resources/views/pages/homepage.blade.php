@extends('layouts.app')

@section('title', 'Home')

@section('content')

<section id="cards">

    <article class="card">
        <form class="new_card">
            <input type="text" name="name" placeholder="new card">
        </form>
    </article>
</section>

@endsection