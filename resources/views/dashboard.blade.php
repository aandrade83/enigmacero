@extends('layouts.enigmacero')

@section('title', 'EnigmaCero - Panel')

@section('top-right')
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="enigmacero-btn-secondary">Cerrar sesión</button>
    </form>
@endsection

@section('content')
@php
    // compatibilidad: si hoy lo pasás por Session o por Auth, no se rompe
    $displayName = $userName ?? (Auth::user()->name ?? 'Usuario');
    $role = $userRole ?? (Auth::user()->role ?? 'admin'); // por ahora default admin
    $isAdmin = ($role === 'admin');
@endphp

<div class="ec-dashboard">
    <aside class="ec-sidebar">
        <div class="ec-sidebar-title">MÓDULOS</div>

        <nav class="ec-nav">
            @if($isAdmin)
                <a class="ec-nav-link is-active" href="#">Usuarios</a>
                <a class="ec-nav-link" href="#">Administración de Clientes</a>
            @endif

            <a class="ec-nav-link" href="#">Visualización de Archivos</a>
            <a class="ec-nav-link" href="#">Carga de Archivos</a>
        </nav>

        <div class="ec-sidebar-footer">
            <div class="ec-role">Rol actual: <strong>{{ $role }}</strong></div>
        </div>
    </aside>

    <section class="ec-main">
        <div class="ec-welcome">
            Bienvenido, <strong>{{ $displayName }}</strong>.
        </div>

        <div class="ec-quote-card">
            <div class="ec-quote-text">
                <strong>{{ $quote ?? 'La inteligencia de negocios comienza con buenas preguntas.' }}</strong>
            </div>
            <div class="ec-quote-author">— {{ $quoteAuthor ?? 'EnigmaCero' }}</div>
        </div>
    </section>
</div>
@endsection
