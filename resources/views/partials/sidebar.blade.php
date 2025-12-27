@php
    $user = auth()->user();

    // Normalizar roles por si vienen en español o con mayúsculas
    $rawRole = strtolower(trim($user->role ?? ''));
    $role = match ($rawRole) {
        'administrador', 'admin' => 'admin',
        'empleado', 'employee' => 'employee',
        'cliente', 'client' => 'client',
        default => $rawRole,
    };

    $isAdmin = $role === 'admin';
    $isEmployee = $role === 'employee';
    $isClient = $role === 'client';

    $active = fn(string $path) => request()->is(trim($path, '/')) || request()->is(trim($path, '/') . '/*');
@endphp

<aside class="ec-sidebar">
    <div class="ec-sidebar-title">MÓDULOS</div>

    <ul class="ec-sidebar-menu">
        @if(!$isClient)
            <li class="ec-sidebar-item {{ $active('dashboard') ? 'active' : '' }}">
                <a href="{{ url('/dashboard') }}">Home</a>
            </li>
        @endif

        @if($isAdmin)
            <li class="ec-sidebar-item {{ $active('users') ? 'active' : '' }}">
                <a href="{{ url('/users') }}">Usuarios</a>
            </li>
        @endif

        @if($isAdmin || $isEmployee)
            <li class="ec-sidebar-item {{ $active('clients') ? 'active' : '' }}">
                <a href="{{ url('/clients') }}">Administración de Clientes</a>
            </li>
        @endif

        <li class="ec-sidebar-item {{ $active('files') ? 'active' : '' }}">
            <a href="{{ url('/files') }}">Visualización de Archivos</a>
        </li>

        <li class="ec-sidebar-item {{ $active('uploads') ? 'active' : '' }}">
            <a href="{{ url('/uploads') }}">Carga de Archivos</a>
        </li>
    </ul>
</aside>
