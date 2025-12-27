@php
    use Illuminate\Support\Facades\Auth;

    $user = Auth::user();
    $role = $user->role ?? 'admin';

    $isAdmin = $role === 'admin';
    $isEmployee = $role === 'employee';
    $isClient = $role === 'client';

    // Activo por ruta
    $isHomeActive    = request()->routeIs('dashboard') || request()->is('dashboard');
    $isUsersActive   = request()->is('users*');
    $isClientsActive = request()->routeIs('clients.*') || request()->is('clients*');
    $isFilesActive   = request()->routeIs('files.*') || request()->is('files*');
    $isUploadsActive = request()->routeIs('uploads.*') || request()->is('uploads*');
@endphp

<aside class="ec-sidebar">
    <div class="ec-sidebar-title">MÓDULOS</div>

    <nav class="ec-nav">
        {{-- HOME: no lo mostramos al cliente (según requerimiento) --}}
        @if(!$isClient)
            <a class="ec-nav-link {{ $isHomeActive ? 'active' : '' }}" href="{{ route('dashboard') }}">
                Home
            </a>
        @endif

        {{-- USUARIOS: SOLO admin --}}
        @if($isAdmin)
            <a class="ec-nav-link {{ $isUsersActive ? 'active' : '' }}" href="{{ route('users.index') }}">
                Usuarios
            </a>
        @endif

        {{-- CLIENTES: admin + employee --}}
        @if($isAdmin || $isEmployee)
            <a class="ec-nav-link {{ $isClientsActive ? 'active' : '' }}" href="{{ route('clients.index') }}">
                Administración de Clientes
            </a>
        @endif

        {{-- ARCHIVOS: admin + employee + client --}}
        @if($isAdmin || $isEmployee || $isClient)
            <a class="ec-nav-link {{ $isFilesActive ? 'active' : '' }}" href="{{ route('files.index') }}">
                Visualización de Archivos
            </a>

            <a class="ec-nav-link {{ $isUploadsActive ? 'active' : '' }}" href="{{ route('uploads.index') }}">
                Carga de Archivos
            </a>
        @endif
    </nav>
</aside>
