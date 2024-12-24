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
            color: rgb(8, 57, 105) !important;
        }

        .btn-custom {
            background-color: rgb(8, 57, 105) !important;
            color: white !important;
        }

        .btn-custom:hover {
            background-color: rgb(8, 57, 105) !important;
            color: white !important;
        }

        /* Sidebar fixa para ecrãs grandes */
        @media (min-width: 992px) { /* lg e acima */
            aside {
                position: fixed;
                top: 70px; /* altura do header */
                left: 0;
                height: calc(100vh - 70px); /* altura total menos o header */
                overflow-y: auto;
            }
            main {
                margin-left: 16.6667%; /* largura da sidebar em lg (col-lg-2) */
            }
        }
    </style>
</head>
<body>
    <header class="bg-white shadow-sm fixed-top">
        <nav class="navbar navbar-expand-md navbar-light bg-white container py-2">
            <div class="container-fluid">
                <!-- Logo e nome da aplicação -->
                <a href="{{ Auth::check() ? route('home') : route('welcome') }}" class="navbar-brand text-custom">
                    <h1 class="mb-0">Connectify</h1>
                </a>

                <!-- Botão toggle para navegação em dispositivos pequenos -->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- Menu responsivo -->
                <div class="collapse navbar-collapse" id="navbarNav">
                    <div class="ms-auto d-flex align-items-center gap-3">
                        <!-- Barra de busca -->
                        <form action="{{ route('search') }}" method="GET" class="d-flex flex-grow-1 flex-md-grow-0 me-md-3">
                            <input 
                                type="text" 
                                name="query" 
                                id="searchInput" 
                                placeholder="Search users, posts, comments, groups..." 
                                class="form-control me-2" 
                                value="{{ request('query') }}" 
                                style="border-radius: 25px;" 
                                required>
                        </form>

                        @auth
                            <a href="{{ route('home') }}" class="btn btn-custom">Friends</a>
                            <a href="{{ route('feed') }}" class="btn btn-custom">
                                <i class="fa-solid fa-house"></i>
                            </a>
                            <a href="{{ route('user', ['id' => Auth::user()->id]) }}" class="d-flex align-items-center text-custom text-decoration-none">
                                <img src="{{ route('profile.picture', parameters: ['id' => Auth::user()->id]) }}" alt="Profile Picture" class="rounded-circle me-2" style="width: 50px; height: 50px; object-fit: cover;">
                                <span>{{ Auth::user()->username }}</span>
                            </a>
                            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-custom">
                                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-custom">Login</a>
                            <a href="{{ route('register') }}" class="btn btn-custom">Register</a>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>
        <div style="background-color: rgb(8, 57, 105); height: 3px;"></div>
    </header>



    <div class="container-fluid mt-5 pt-5">
        <div class="row">
            <!-- Sidebar -->
            <aside class="col-12 col-md-3 col-lg-2 bg-light p-3 border-end">
                @auth
                <div class="mb-3 d-flex flex-column gap-2">
                    <a href="{{ route('tagged.posts') }}" class="btn btn-custom">Tagged Posts</a>
                    <a href="{{ route('saved.posts') }}" class="btn btn-custom">Saved</a>
                    @can('createUser', App\Models\User::class)
                        <a href="{{ route('user.create') }}" class="btn btn-custom">New User</a>
                    @endcan
                    <a href="{{ route('group.create') }}" class="btn btn-custom">New Group</a>
                    <a href="{{ route('post.create') }}" class="btn btn-custom">New Post</a>
                </div>
                @endauth

                @include('partials.group-list', ['groups' => $allGroups])
            </aside>

            <!-- Content Area -->
            <main class="col-12 col-md-9 col-lg-9 p-4">
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
        <a href="{{ route('contact') }}" class="text-decoration-none text-custom fw-bold">Contact Us</a>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
