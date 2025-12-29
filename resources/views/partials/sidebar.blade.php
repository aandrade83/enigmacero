<div class="ec-sidebar">
  <div class="ec-sidebar-title">MÓDULOS</div>

  <nav class="ec-nav">
    @php
      $user = auth()->user();
      $role = $user?->role;
    @endphp

    {{-- ADMIN: everything --}}
    @if($role === 'admin')
      <a href="{{ route('dashboard') }}" class="ec-nav-link {{ request()->is('dashboard') ? 'active' : '' }}">
        <span class="ec-dot">•</span> Home
      </a>

      <a href="{{ route('users.index') }}" class="ec-nav-link {{ request()->is('users*') ? 'active' : '' }}">
        <span class="ec-dot">•</span> Usuarios
      </a>

      <a href="{{ route('clients.index') }}" class="ec-nav-link {{ request()->is('clients*') ? 'active' : '' }}">
        <span class="ec-dot">•</span> Administración de Clientes
      </a>

      <a href="{{ route('files.index') }}" class="ec-nav-link {{ request()->is('files*') ? 'active' : '' }}">
        <span class="ec-dot">•</span> Visualización de Archivos
      </a>

      <a href="{{ route('uploads.index') }}" class="ec-nav-link {{ request()->is('uploads*') ? 'active' : '' }}">
        <span class="ec-dot">•</span> Carga de Archivos
      </a>

    {{-- EMPLOYEE: no users, no delete actions (enforced server-side) --}}
    @elseif($role === 'employee')
      <a href="{{ route('dashboard') }}" class="ec-nav-link {{ request()->is('dashboard') ? 'active' : '' }}">
        <span class="ec-dot">•</span> Home
      </a>

      <a href="{{ route('clients.index') }}" class="ec-nav-link {{ request()->is('clients*') ? 'active' : '' }}">
        <span class="ec-dot">•</span> Administración de Clientes
      </a>

      <a href="{{ route('files.index') }}" class="ec-nav-link {{ request()->is('files*') ? 'active' : '' }}">
        <span class="ec-dot">•</span> Visualización de Archivos
      </a>

      <a href="{{ route('uploads.index') }}" class="ec-nav-link {{ request()->is('uploads*') ? 'active' : '' }}">
        <span class="ec-dot">•</span> Carga de Archivos
      </a>

    {{-- CLIENT: only files and uploads --}}
    @else
      <a href="{{ route('files.index') }}" class="ec-nav-link {{ request()->is('files*') ? 'active' : '' }}">
        <span class="ec-dot">•</span> Visualización de Archivos
      </a>

      <a href="{{ route('uploads.index') }}" class="ec-nav-link {{ request()->is('uploads*') ? 'active' : '' }}">
        <span class="ec-dot">•</span> Carga de Archivos
      </a>
    @endif
  </nav>
</div>
