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
        <link href="{{ url('css/app.css') }}" rel="stylesheet">
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
                @if (Auth::check())
                    <!-- Link para o perfil do usuário -->
                    <a href="{{ route('user', ['id' => Auth::user()->id]) }}" class="username-link">
                        <span>{{ Auth::user()->username }}</span>
                    </a>
                    <a class="button" href="{{ url('/logout') }}">Logout</a>
                @endif
            </header>
            <section id="content">
                @yield('content')
            </section>
        </main>
    </body>
</html>