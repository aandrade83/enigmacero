<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'EnigmaCero')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Fuente (puedes cambiarla luego por la misma de la web exacta) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Nuestro CSS estático --}}
    <link rel="stylesheet" href="{{ asset('css/enigmacero.css') }}">
</head>
<body>
<div class="enigmacero-shell">
    <header class="enigmacero-header">
        <div>
            <img src="{{ asset('enigmacero/EnigmaCero.svg') }}"
                 alt="EnigmaCero"
                 class="enigmacero-logo">
        </div>

        {{-- Aquí podríamos poner links como "Acerca de", "¿Cómo funciona?", etc. más adelante --}}
        <nav>
            @yield('top-right')
        </nav>
    </header>

    <main class="enigmacero-main">
        @yield('content')
    </main>
</div>
</body>
</html>
