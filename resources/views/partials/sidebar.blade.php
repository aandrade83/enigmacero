@php
    $user = auth()->user();
    $role = $user?->role;
@endphp

<aside class="ec-sidebar">
    <div class="ec-sidebar-brand">
        <img src="{{ asset('enigmacero/EnigmaCero.svg') }}" alt="EnigmaCero" class="ec-logo">
    </div>

    <div class="ec-sidebar-title">MÓDULOS</div>

    <nav class="ec-nav">
        <a href="{{ route('dashboard') }}" class="ec-nav-link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">
            <span class="ec-nav-dot"></span>
            Home
        </a>

        @if($role === 'admin')
            <a href="{{ route('users.index') }}" class="ec-nav-link {{ request()->routeIs('users.*') ? 'is-active' : '' }}">
                <span class="ec-nav-dot"></span>
                Usuarios
            </a>

            <a href="{{ route('clients.index') }}" class="ec-nav-link {{ request()->routeIs('clients.*') ? 'is-active' : '' }}">
                <span class="ec-nav-dot"></span>
                Administración de Clientes
            </a>
        @endif

        <a href="{{ route('files.index') }}" class="ec-nav-link {{ request()->routeIs('files.*') ? 'is-active' : '' }}">
            <span class="ec-nav-dot"></span>
            Visualización de Archivos
        </a>

        <a href="{{ route('uploads.index') }}" class="ec-nav-link {{ request()->routeIs('uploads.*') ? 'is-active' : '' }}">
            <span class="ec-nav-dot"></span>
            Carga de Archivos
        </a>
    </nav>
</aside>
