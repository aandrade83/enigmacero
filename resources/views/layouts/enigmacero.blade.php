<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'EnigmaCero')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">

    @php
        $cssPath = public_path('css/enigmacero.css');
        $cssVersion = @filemtime($cssPath) ?: time(); // evita 500 si algo raro pasa con filemtime
    @endphp

    <link rel="stylesheet" href="{{ asset('css/enigmacero.css') }}?v={{ $cssVersion }}">

    <style>
        :root{
            --ec-grid-url: url("{{ asset('enigmacero/Pattern-Grid.svg') }}");
        }
        body{
            background-image: var(--ec-grid-url);
            background-repeat: repeat;
            background-size: 220px 220px;
        }
    </style>
</head>

<body class="enigmacero-page">
    <div class="enigmacero-shell">
        <header class="enigmacero-header">
            <div class="enigmacero-brand">
                <img class="enigmacero-logo" src="{{ asset('enigmacero/EnigmaCero.svg') }}" alt="EnigmaCero">
            </div>

            <div class="enigmacero-header-actions">
                @yield('top-right')
            </div>
        </header>

        <main class="enigmacero-content">
            @yield('content')
        </main>
    </div>
</body>
</html>
