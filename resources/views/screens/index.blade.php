<!DOCTYPE html>
<html lang="nl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Alle schermen | Portfolio Studio</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="screens-page">
        <main class="screens-shell">
            <header class="screens-hero">
                <p class="eyebrow">PORTFOLIO STUDIO / SCHERMEN</p>
                <h1>Alle schermen op één plek.</h1>
                <p>Een overzicht van de pagina's in het studentenportaal. Open een scherm om het in de browser te bekijken.</p>
            </header>

            <section class="screen-grid" aria-label="Overzicht van schermen">
                <a class="screen-card" href="{{ url('/') }}">
                    <span class="screen-number">01</span><span class="tag">Openbaar</span>
                    <h2>Welkom</h2><p>De startpagina van Portfolio Studio.</p><strong>Scherm openen ↗</strong>
                </a>
                <a class="screen-card" href="{{ route('register') }}">
                    <span class="screen-number">02</span><span class="tag">Aanmelden</span>
                    <h2>Registreren</h2><p>Een nieuw studentenaccount aanmaken.</p><strong>Scherm openen ↗</strong>
                </a>
                <a class="screen-card" href="{{ route('login') }}">
                    <span class="screen-number">03</span><span class="tag">Aanmelden</span>
                    <h2>Inloggen</h2><p>Inloggen met wachtwoord of Google.</p><strong>Scherm openen ↗</strong>
                </a>
                <a class="screen-card" href="{{ route('dashboard') }}">
                    <span class="screen-number">04</span><span class="tag">Student</span>
                    <h2>Mijn portfolio</h2><p>Werkprocessen, bewijsstukken en portfolio-informatie.</p><strong>Scherm openen ↗</strong>
                </a>
                <a class="screen-card" href="{{ route('profile.edit') }}">
                    <span class="screen-number">05</span><span class="tag">Student</span>
                    <h2>Profiel</h2><p>Profielgegevens, wachtwoord en accountbeheer.</p><strong>Scherm openen ↗</strong>
                </a>
            </section>

            <p class="screens-tip">Het dashboard en profiel vragen om eerst in te loggen.</p>
        </main>
    </body>
</html>