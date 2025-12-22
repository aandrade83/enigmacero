@php
    $displayName = $userName ?? (Auth::user()->name ?? 'Usuario');
    $role = $userRole ?? (Auth::user()->role ?? 'admin');
    $isAdmin = ($role === 'admin');

    $isHomeActive    = request()->routeIs('dashboard');
    $isUsersActive   = request()->routeIs('users.*') || request()->is('users*');
    $isClientsActive = request()->routeIs('clients.*') || request()->is('clients*');
    $isUploadsActive = request()->routeIs('uploads.*') || request()->is('uploads*');
    $isFilesActive   = request()->routeIs('files.*')   || request()->is('files*');
@endphp

<aside class="ec-sidebar">
    <div class="ec-sidebar-title">MÓDULOS</div>

    <nav class="ec-nav">
        {{-- HOME siempre --}}
        <a class="ec-nav-link {{ $isHomeActive ? 'is-active' : '' }}"
           href="{{ route('dashboard') }}">
            Home
        </a>

        @if($isAdmin)
            <a class="ec-nav-link {{ $isUsersActive ? 'is-active' : '' }}"
               href="{{ route('users.index') }}">
                Usuarios
            </a>

            <a class="ec-nav-link {{ $isClientsActive ? 'is-active' : '' }}"
               href="{{ route('clients.index') }}">
                Administración de Clientes
            </a>
        @endif

        <a class="ec-nav-link {{ $isFilesActive ? 'is-active' : '' }}"
           href="{{ route('files.index') }}">
           Visualización de Archivos
        </a>

        <a class="ec-nav-link {{ $isUploadsActive ? 'is-active' : '' }}"
           href="{{ route('uploads.index') }}">
           Carga de Archivos
        </a>
    </nav>

    <div class="ec-sidebar-footer">
        <div class="ec-role">Rol actual: <strong>{{ $role }}</strong></div>
    </div>
</aside>
