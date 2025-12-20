<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'EnigmaCero')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @php
        $cssPath = public_path('css/enigmacero.css');
        $cssVersion = @filemtime($cssPath) ?: time();
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

    @yield('head')
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

    {{-- Flash messages (SweetAlert) --}}
    @if (session('success'))
        <script>
            window.__EC_FLASH_SUCCESS = @json(session('success'));
        </script>
    @endif

    @if (session('error'))
        <script>
            window.__EC_FLASH_ERROR = @json(session('error'));
        </script>
    @endif

    @if ($errors->any())
        <script>
            window.__EC_FLASH_ERROR = @json($errors->first());
        </script>
    @endif

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            if (window.Swal && window.__EC_FLASH_SUCCESS) {
                Swal.fire({
                    icon: 'success',
                    title: 'Listo',
                    text: String(window.__EC_FLASH_SUCCESS),
                    timer: 1600,
                    showConfirmButton: false,
                });
            }
            if (window.Swal && window.__EC_FLASH_ERROR) {
                Swal.fire({
                    icon: 'error',
                    title: 'Ups',
                    text: String(window.__EC_FLASH_ERROR),
                });
            }
        });
    </script>

    @yield('page-scripts')
</body>
</html>
