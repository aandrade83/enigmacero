@php
    // compatibilidad: si hoy lo pasás por Session o por Auth, no se rompe
    $displayName = $userName ?? (Auth::user()->name ?? 'Usuario');
    $role = $userRole ?? (Auth::user()->role ?? 'admin'); // por ahora default admin
    $isAdmin = ($role === 'admin');

    // Activo por ruta
    // - "Usuarios" lo amarramos al dashboard (y opcionalmente a /users si luego existe)
    // - "Clientes" a cualquier ruta /clients
    $isUsersActive   = request()->routeIs('dashboard') || request()->is('users*');
    $isClientsActive = request()->routeIs('clients.*') || request()->is('clients*');
@endphp

<aside class="ec-sidebar">
    <div class="ec-sidebar-title">MÓDULOS</div>

    <nav class="ec-nav">
        @if($isAdmin)
            <a class="ec-nav-link {{ $isUsersActive ? 'is-active' : '' }}"
               href="{{ route('dashboard') }}">
                Usuarios
            </a>

            <a class="ec-nav-link {{ $isClientsActive ? 'is-active' : '' }}"
               href="{{ route('clients.index') }}">
                Administración de Clientes
            </a>
        @endif

        <a class="ec-nav-link" href="#">Visualización de Archivos</a>
        <a class="ec-nav-link" href="#">Carga de Archivos</a>
    </nav>

    <div class="ec-sidebar-footer">
        <div class="ec-role">Rol actual: <strong>{{ $role }}</strong></div>
    </div>
</aside>

