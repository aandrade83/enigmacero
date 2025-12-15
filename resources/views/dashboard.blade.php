@extends('layouts.enigmacero')

@section('title', 'Panel principal')

@section('content')
<div class="dashboard-layout">

    {{-- Menú lateral --}}
    <aside class="dashboard-sidebar">
        <div class="sidebar-header">
            <span class="sidebar-title">EnigmaCero™</span>
            <span class="sidebar-subtitle">Herramientas de análisis</span>
        </div>

        @php
            $user = Auth::user();
        @endphp

        <nav class="sidebar-nav">
            {{-- Solo ADMIN ve Usuarios y Administración de clientes --}}
            @if($user && $user->role === 'admin')
                <a href="#" class="sidebar-link">
                    <span class="sidebar-link-label">Usuarios</span>
                    <span class="sidebar-link-badge">Admin</span>
                </a>

                <a href="#" class="sidebar-link">
                    <span class="sidebar-link-label">Administración de Clientes</span>
                </a>
            @endif

            <a href="#" class="sidebar-link">
                <span class="sidebar-link-label">Visualización de Archivos</span>
            </a>

            <a href="#" class="sidebar-link">
                <span class="sidebar-link-label">Carga de Archivos</span>
            </a>
        </nav>
    </aside>

    {{-- Contenido principal --}}
    <section class="dashboard-main">

        {{-- Barra superior con saludo y botón de logout --}}
        <header class="dashboard-topbar">
            <div class="topbar-user">
                <span class="topbar-user-label">Bienvenido,</span>
                <span class="topbar-user-name">
                    {{ $userName ?? ($user->name ?? 'Usuario') }}
                </span>
            </div>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn-logout">
                    Cerrar sesión
                </button>
            </form>
        </header>

        {{-- Tarjeta central --}}
        <div class="dashboard-card">
            <h1 class="dashboard-title">Panel principal</h1>

            <p class="dashboard-subtitle">
                Aquí vamos a ir mostrando los módulos de EnigmaCero
                (análisis, reportes, etc.) conforme los vayamos construyendo.
            </p>

            {{-- Frase inspiradora --}}
            <div class="quote-card">
                <h2 class="quote-title">Frase inspiradora de hoy</h2>

                <p class="quote-text">
                    {{ $dailyQuote['text'] ?? 'La inteligencia de negocios comienza con buenas preguntas.' }}
                </p>

                @if(!empty($dailyQuote['author']))
                    <p class="quote-author">— {{ $dailyQuote['author'] }}</p>
                @endif
            </div>
        </div>
    </section>
</div>
@endsection
