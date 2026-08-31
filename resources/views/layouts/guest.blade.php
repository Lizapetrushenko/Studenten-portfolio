<!DOCTYPE html>
<html lang="nl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="deltion-auth">
        <header class="deltion-nav auth-nav">
            <a href="{{ url('/') }}" aria-label="Deltion College">
                <img class="deltion-logo-image" src="{{ asset('images/deltionlogo.webp') }}" alt="Deltion College">
            </a>
        </header>
        <main class="auth-main">
            <div class="auth-card">
                <p class="deltion-kicker">DELTION COLLEGE / STUDENTENPORTAAL</p>
                <h1 class="auth-title">Welkom terug</h1>
                <p class="auth-subtitle">Log in om verder te werken aan je portfolio.</p>
                {{ $slot }}
            </div>
        </main>
    </body>
</html>
