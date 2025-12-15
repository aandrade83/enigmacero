<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>EnigmaCero – Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ asset('css/enigmacero.css') }}">
</head>
<body class="enigmacero-page">

<div class="enigmacero-grid-bg">
    <div class="ec-dashboard-layout">

        {{-- SIDEBAR IZQUIERDA --}}
        <aside class="ec-sidebar">
            <div class="ec-logo-row">
                <img src="{{ asset('enigmacero/EnigmaCero.svg') }}" alt="EnigmaCero" class="ec-logo">
              
            </div>

            <div class="ec-menu">
                <div class="ec-menu-section-title">Módulos</div>
                <ul class="ec-menu-list">
                    <li class="ec-menu-item">
                        {{-- Por ahora solo marcamos Usuarios como activo --}}
                        <a href="#" class="ec-menu-link is-active">
                            <span class="ec-menu-dot"></span>
                            Usuarios
                        </a>
                    </li>
                    <li class="ec-menu-item">
                        <a href="#" class="ec-menu-link">
                            <span class="ec-menu-dot"></span>
                            Administración de Clientes
                        </a>
                    </li>
                    <li class="ec-menu-item">
                        <a href="#" class="ec-menu-link">
                            <span class="ec-menu-dot"></span>
                            Visualización de Archivos
                        </a>
                    </li>
                    <li class="ec-menu-item">
                        <a href="#" class="ec-menu-link">
                            <span class="ec-menu-dot"></span>
                            Carga de Archivos
                        </a>
                    </li>
                </ul>
            </div>
               {{-- TOPBAR con botón Cerrar sesión a la DERECHA --}}
            <div class="ec-topbar">
                <form method="POST" action="{{ route('logout') }}" class="ec-logout-form">
                    @csrf
                    <button type="submit" class="ec-logout-btn">
                        Cerrar sesión
                    </button>
                </form>
            </div>

            <div class="ec-sidebar-footer">
                 <form method="POST" action="{{ route('logout') }}" class="ec-logout-form">
                    @csrf
                    <button type="submit" class="ec-logout-btn">
                        Cerrar sesión
                    </button>
                </form>
                
                Rol actual:
                <strong>{{ auth()->user()->role ?? 'admin' }}</strong>
            </div>
        </aside>

        {{-- CONTENIDO PRINCIPAL --}}
        <main class="ec-main">
         
          

            {{-- TARJETA CENTRAL (solo frase) --}}
            <section class="ec-card">
                <p class="ec-card-subtitle">
                    Bienvenido,
                    <strong>{{ auth()->user()->name ?? 'Administrador EnigmaCero' }}</strong>.
                </p>

                <div class="ec-quote-block">
                    <p class="ec-quote-text">
                        <strong>
                            {{ $quote['text'] ?? 'La inteligencia de negocios comienza con buenas preguntas.' }}
                        </strong>
                    </p>

                    @if(!empty($quote['author']))
                        <p class="ec-quote-author">— {{ $quote['author'] }}</p>
                    @else
                        <p class="ec-quote-author">— EnigmaCero</p>
                    @endif
                </div>
            </section>
        </main>

    </div>
</div>

</body>
</html>
