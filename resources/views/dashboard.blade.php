<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel principal - EnigmaCero</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- CSS principal de EnigmaCero --}}
    <link rel="stylesheet" href="{{ asset('css/enigmacero.css') }}">
</head>
<body class="enigmacero-page">
    <div class="enigmacero-grid-bg">
        @php
            $user    = auth()->user();
            $role    = $user->role ?? 'user';
            $userName = $user->name ?? 'Usuario';
        @endphp

        {{-- HEADER SUPERIOR --}}
        <header class="ec-header">
            <div class="ec-header-left">
                <img src="{{ asset('enigmacero/EnigmaCero.svg') }}"
                     alt="EnigmaCero"
                     class="ec-logo">
            </div>

            <div class="ec-header-right">
                {{-- MENÚ DE MÓDULOS --}}
                <nav class="ec-nav">
                    {{-- Solo admin ve Usuarios y Administración de Clientes --}}
                    @if($role === 'admin')
                        <a href="#" class="ec-nav-link ec-nav-link--primary" title="Módulo de usuarios (próximamente)">
                            Usuarios
                        </a>
                        <a href="#" class="ec-nav-link" title="Administración de clientes (próximamente)">
                            Administración de Clientes
                        </a>
                    @endif

                    {{-- Todos ven estos dos módulos (links aún de ejemplo) --}}
                    <a href="#" class="ec-nav-link" title="Visualización de archivos (próximamente)">
                        Visualización de Archivos
                    </a>
                    <a href="#" class="ec-nav-link" title="Carga de archivos (próximamente)">
                        Carga de Archivos
                    </a>
                </nav>

                {{-- BOTÓN CERRAR SESIÓN ARRIBA A LA DERECHA --}}
                <form method="POST" action="{{ route('logout') }}" class="ec-logout-form">
                    @csrf
                    <button type="submit" class="ec-logout-btn">
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </header>

        {{-- CONTENIDO CENTRAL --}}
        <main class="ec-main">
            <section class="ec-dashboard-card">
                <h1 class="ec-dashboard-title">Panel principal</h1>

                <p class="ec-dashboard-subtitle">
                    Bienvenido, <strong>{{ $userName }}</strong>.
                </p>

                <p class="ec-dashboard-text">
                    Aquí vamos a ir mostrando los módulos de EnigmaCero
                    (análisis, reportes, etc.) conforme los vayamos construyendo.
                </p>

                {{-- FRASE INSPIRADORA DESDE API --}}
                <div class="ec-quote-box">
                    <p class="ec-quote-label">Frase inspiradora de hoy</p>
                    <p class="ec-quote-text" id="quote-text">
                        Cargando frase inspiradora...
                    </p>
                    <p class="ec-quote-author" id="quote-author"></p>
                </div>
            </section>
        </main>
    </div>

    {{-- SCRIPT: obtiene frase desde API pública (quotable.io) --}}
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const textEl   = document.getElementById('quote-text');
            const authorEl = document.getElementById('quote-author');

            try {
                const response = await fetch('https://api.quotable.io/random');
                if (!response.ok) {
                    throw new Error('Error HTTP ' + response.status);
                }

                const data = await response.json();
                textEl.textContent   = '“' + data.content + '”';
                authorEl.textContent = '— ' + data.author;
            } catch (error) {
                // Si falla el API, mostramos una frase por defecto
                textEl.textContent = '“La inteligencia de negocios comienza con buenas preguntas.”';
                authorEl.textContent = '';
            }
        });
    </script>
</body>
</html>
