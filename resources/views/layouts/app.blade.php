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

        <script type="text/javascript">
            // Fix for Firefox autofocus CSS bug
            // See: http://stackoverflow.com/questions/18943276/html-5-autofocus-messes-up-css-loading/18945951#18945951
        </script>
        <script type="text/javascript" src={{ url('js/app.js') }} defer>

        </script>
    </head>
    <body>
        <main>
            <header>
                <h1><a href="{{ url('/home') }}">Connectify</a></h1>
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
                            // Quando pressionar Enter (código 13), envia o formulário
                            if (event.key === 'Enter') {
                                document.getElementById('searchForm').submit();
                            }
                        });
                    </script>


                    @if (Auth::check())
                        <div class="user-actions">
                            <a href="{{ route('post.create') }}" class="button new-post-button">New Post</a>



                            <!-- Link para o perfil do usuário -->
                            <a href="{{ route('user', ['id' => Auth::user()->id]) }}" class="username-link">
                                <span>{{ Auth::user()->username }}</span>
                            </a>

                            <!-- Logout -->
                            <a href="{{ route('logout') }}" class="logout-link">Logout</a>
                        </div>
                    @endif
                </div>
            </header>
            <section id="content">
                @yield('content')
            </section>
        </main>
    </body>
</html>
