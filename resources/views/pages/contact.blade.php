@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 600px; margin: auto; padding: 40px; text-align: center;">
    <h1 class="mb-4">Contact Us</h1>

    @if(session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @elseif(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <p class="mb-4">If you have any questions, suggestions, or need support, please fill out the form below. We are here to help!</p>

    <form action="{{ route('contact.send') }}" method="POST" novalidate>
        @csrf

        <div class="form-group mb-3 text-start">
            <label for="username" class="form-label">Your Name</label>
            <input 
                type="text" 
                name="username" 
                id="username" 
                class="form-control @error('username') is-invalid @enderror" 
                value="{{ old('username') }}" 
                required
                placeholder="Enter your name"
            >
            @error('username') 
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group mb-3 text-start">
            <label for="subject" class="form-label">Subject</label>
            <input 
                type="text" 
                name="subject" 
                id="subject" 
                class="form-control @error('subject') is-invalid @enderror" 
                value="{{ old('subject') }}" 
                required
                placeholder="Enter the subject"
            >
            @error('subject') 
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group mb-3 text-start">
            <label for="message" class="form-label">Message</label>
            <textarea 
                name="message" 
                id="message" 
                rows="5" 
                class="form-control @error('message') is-invalid @enderror" 
                required
                placeholder="Describe your question or issue">{{ old('message') }}</textarea>
            @error('message') 
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary w-100" style="margin-top: 20px;">Send Message</button>
    </form>
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
