<!DOCTYPE html>
<html lang="nl">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Bewijsstukkenplan | Deltion College</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="deltion-home">
        <header class="deltion-nav">
            <a href="{{ url('/') }}" aria-label="Deltion College">
                <img class="deltion-logo-image" src="{{ asset('images/deltionlogo.webp') }}" alt="Deltion College">
</a>
        </header>
        <main class="deltion-main">
            <div class="deltion-copy">
                <p class="deltion-kicker">DELTION COLLEGE / BEWIJSSTUKKENPLAN</p>
                <h1>Maak je<br><em>bewijsstukkenplan</em><br>makkelijker</h1>
                <p class="deltion-subtitle">Werk je werkprocessen uit, schrijf ideeën en notities op en houd eenvoudig je voortgang bij.</p>
                <div class="deltion-actions">
                    <a class="deltion-button orange" href="{{ route('login') }}">Log in</a>
                    <a class="deltion-button blue" href="{{ route('register') }}">Registreren</a>
                </div>
            </div>
            <div class="deltion-shape" aria-hidden="true"><span></span></div>
        </main>
    </body>
</html>
