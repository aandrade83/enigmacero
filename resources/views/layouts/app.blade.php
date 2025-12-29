<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'EnigmaCero')</title>

    @php
        $cssPath = public_path('css/enigmacero.css');
        $cssVersion = file_exists($cssPath) ? filemtime($cssPath) : time();
    @endphp
    <link rel="stylesheet" href="{{ asset('css/enigmacero.css') }}?v={{ $cssVersion }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/js/app.js'])
</head>
<body class="ec-body">

<div class="ec-app">
    @include('partials.sidebar')

    <main class="ec-main">
        @yield('content')
    </main>
</div>

</body>
</html>
