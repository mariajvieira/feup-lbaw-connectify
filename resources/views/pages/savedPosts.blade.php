@extends('layouts.app')



@section('content')
<div class="row mt-4">
    <div class="col-md-8 offset-md-2">
        <h5 class="mb-3">Saved Posts</h5>
        @if($posts->isEmpty())
            <p>No post saved yet.</p>
        @else
        <div class="mb-4">
            @include('pages.posts', ['posts' => $posts])
        </div>
        @endif
    </div>
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
