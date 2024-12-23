<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script type="text/javascript" src="{{ url('js/app.js') }}" defer></script>

    <style>
        .text-custom {
            color:rgb(8, 57, 105) !important;
        }
        .btn-custom {
        background-color: rgb(8, 57, 105) !important;
        color: white !important;
        }

        .btn-custom:hover {
            background-color: rgb(8, 57, 105) !important;
            color: white !important;
        }
    </style>
</head>
<body>
    <header class="bg-white shadow-sm fixed-top">
        <div class="container d-flex justify-content-between align-items-center py-3">
            <div class="d-flex align-items-center">
                <h1 class="mb-0 me-3">
                    <a href="{{ Auth::check() ? route('home') : route('welcome') }}" class="text-decoration-none text-custom">
                        Connectify
                    </a>
                </h1>
                <!-- Barra de busca -->
                <form action="{{ route('search') }}" method="GET" class="d-flex ms-auto">
                    <input 
                        type="text" 
                        name="query" 
                        id="searchInput" 
                        placeholder="Search users, posts, comments, groups..." 
                        class="form-control me-2" 
                        value="{{ request('query') }}" 
                        style="width: 400px; border-radius: 25px;" 
                        required>
                </form>

            </div>
            <div class="d-flex align-items-center gap-3">
                @auth
                    <a href="{{ route('home') }}" class="btn btn-custom">Friends</a>
                    <a href="{{ route('feed') }}" class="btn btn-custom">
                        <i class="fa-solid fa-house me-2"></i>
                    </a>
                    <a href="{{ route('user', ['id' => Auth::user()->id]) }}" class="d-flex align-items-center text-custom text-decoration-none">
                        <img src="{{ route('profile.picture', parameters: ['id' => Auth::user()->id]) }}" alt="Profile Picture" class="rounded-circle me-2" style="width: 50px; height: 50px; object-fit: cover;">

                        <span>{{ Auth::user()->username }}</span>
                    </a>
                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-custom">
                            <i class="fa-solid fa-right-from-bracket me-2"></i> Logout
                        </button>
                    </form>

                @else
                    <a href="{{ route('login') }}" class="btn btn-custom">Login</a>
                    <a href="{{ route('register') }}" class="btn btn-custom">Register</a>
                @endauth
            </div>
        </div>
        <div style="background-color:rgb(8, 57, 105); height: 3px;"></div>
    </header>

    <div class="container-fluid mt-5 pt-5">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 position-fixed top-40 start-0 vh-100 p-3" style="z-index: 1030;">
                @auth
                <div class="mb-3">
                    <a href="{{ route('tagged.posts') }}" class="btn btn-custom w-100 mb-2">Tagged Posts</a>
                    <a href="{{ route('saved.posts') }}" class="btn btn-custom w-100 mb-2">Saved</a>
                    @can('createUser', App\Models\User::class)
                        <a href="{{ route('user.create') }}" class="btn btn-custom w-100 mb-2">New User</a>
                    @endcan
                    <a href="{{ route('group.create') }}" class="btn btn-custom w-100 mb-2">New Group</a>
                    <a href="{{ route('post.create') }}" class="btn btn-custom w-100 mb-2">New Post</a>
                </div>
                @endauth

                @include('partials.group-list', ['groups' => $allGroups])

            </div>


            <!-- Content Area -->
            <main class="col-md-6 col-lg-8 offset-md-3 offset-lg-2">
                <section id="content">
                    @yield('content')
                </section>
            </main>
        </div>
    </div>


    <footer class="text-center py-3 mt-auto bg-light">
        <p class="text-decoration-none text-custom fw-bold">Connectify 2024</p>
        <a href="{{ route('about') }}" class="text-decoration-none text-custom fw-bold">About Us</a> |
        <a href="{{ route('mainfeatures') }}" class="text-decoration-none text-custom fw-bold">Main Features</a> |
        <a href="{{ route('about') }}" class="text-decoration-none text-custom fw-bold">Help</a> |
        <a href="{{ route('contact') }}" class="text-decoration-none text-custom fw-bold">Contact Us</a>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
