@php
    // compatibilidad: si hoy lo pasás por Session o por Auth, no se rompe
    $displayName = $userName ?? (Auth::user()->name ?? 'Usuario');
    $role = $userRole ?? (Auth::user()->role ?? 'admin'); // por ahora default admin
    $isAdmin = ($role === 'admin');
@endphp

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
