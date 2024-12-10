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
    <link href="{{ url('css/milligram.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <script type="text/javascript" src="{{ url('js/app.js') }}" defer></script>
</head>
<body>
    <main>
    <header>
    <h1>
        <a href="{{ Auth::check() ? route('home') : route('welcome') }}">Connectify</a>
    </h1>
    <div class="header-actions">
        <!-- Barra de busca -->
            <form action="{{ route('search') }}" method="GET" class="search-form">
                <input 
                    type="text" 
                    name="query" 
                    id="searchInput" 
                    placeholder="Search posts, users..." 
                    class="search-input" 
                    value="{{ request('query') }}" 
                    required>
            </form>

            <script type="text/javascript">
                document.getElementById('searchInput').addEventListener('keydown', function(event) {
                    if (event.key === 'Enter') {
                        document.getElementById('searchForm').submit();
                    }
                });
            </script>

        <div class="user-actions">
            @auth

                @can('createUser', App\Models\User::class)
                    <a href="{{ route('user.create') }}" class="button new-user-button">New User</a>
                @endcan
                <!-- Link para a Home (posts dos amigos) -->
                <a href="{{ route('home') }}" class="button">Friends</a>

                <!-- Link para o Feed (posts públicos + amigos) -->
                <a href="{{ route('feed') }}" class="button">Feed</a>

                <!-- Link para criar novo post -->
                <a href="{{ route('post.create') }}" class="button new-post-button">New Post</a>

                <!-- Link para o perfil do usuário -->
                <a href="{{ route('user', ['id' => Auth::user()->id]) }}" class="username-link" style="display: flex; align-items: center; text-decoration: none;">
                    <img src="{{ asset(Auth::user()->profile_picture) }}" alt="Profile Picture" 
                        style="width: 50px; height: 50px; border-radius: 50%; margin-right: 10px; object-fit: cover;">
                    <span>{{ Auth::user()->username }}</span>
                </a>


                <!-- Logout -->
                <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="logout-link">Logout</button>
                </form>
            @else
                <!-- Links para visitantes -->
                <a href="{{ route('login') }}" class="button login-button">Login</a>
                <a href="{{ route('register') }}" class="button register-button">Register</a>
            @endauth
        </div>
    </div>
</header>

        <section id="content">
            @yield('content')
        </section>
    </main>
</body>
</html>