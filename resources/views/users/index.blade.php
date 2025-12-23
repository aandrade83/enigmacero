@extends('layouts.enigmacero')

@section('title', 'EnigmaCero - Usuarios')

@section('top-right')
<form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit" class="enigmacero-btn-ghost">Cerrar sesión</button>
</form>
@endsection

@section('content')
<div class="ec-dashboard">
    @include('partials.sidebar')

    <section class="ec-main">
        <div class="ec-content-header">
            <div>
                <div class="ec-page-kicker">Administración</div>
                <h1 class="ec-page-title">Usuarios</h1>
            </div>

            <div class="ec-toolbar">
                <a href="{{ route('users.create') }}" class="ec-btn ec-btn-primary ec-btn-with-icon">
                    <span class="ec-btn-icon" aria-hidden="true">+</span>
                    Agregar usuario
                </a>
            </div>
        </div>

        @if (session('success'))
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    if (window.Swal) {
                        Swal.fire({ icon: 'success', title: 'Listo', text: @json(session('success')) });
                    }
                });
            </script>
        @endif

        @if ($errors->any())
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Revisa los campos',
                            html: @json('<ul style="text-align:left; margin:0; padding-left:1.25rem;">' . implode('', $errors->all('<li>:message</li>')) . '</ul>')
                        });
                    }
                });
            </script>
        @endif

        {{-- Administradores --}}
        <div class="ec-card ec-card-pad ec-section-card" style="margin-top: 14px;">
            <h2 class="ec-section-title">Administradores</h2>

            <div class="ec-table-wrap">
                <table class="ec-table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Activo</th>
                            <th class="ec-th-actions">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($admins as $u)
                            <tr>
                                <td>
                                    <div class="ec-row-title">{{ $u->name }}</div>
                                </td>
                                <td class="ec-muted">{{ $u->email }}</td>
                                <td>
                                    @if($u->is_active)
                                        <span class="ec-badge ec-badge-success">Sí</span>
                                    @else
                                        <span class="ec-badge ec-badge-muted">No</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="ec-actions">
                                        <a class="ec-icon-btn" href="{{ route('users.edit', $u->id) }}" title="Editar">
                                            <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true">
                                                <path fill="currentColor" d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zm2.92 2.33H5v-.92l9.06-9.06.92.92L5.92 19.58zM20.71 7.04a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                                            </svg>
                                        </a>

                                        <form method="POST" action="{{ route('users.destroy', $u->id) }}" class="ec-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="ec-icon-btn ec-icon-btn-danger js-delete-user"
                                                    data-name="{{ $u->name }}" title="Eliminar">
                                                <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path fill="currentColor" d="M6 7h12l-1 14H7L6 7zm3-3h6l1 2H8l1-2z"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="ec-empty">Aún no hay usuarios en este rol.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Empleados --}}
        <div class="ec-card ec-card-pad ec-section-card" style="margin-top: 14px;">
            <h2 class="ec-section-title">Usuarios / Empleados</h2>

            <div class="ec-table-wrap">
                <table class="ec-table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Activo</th>
                            <th class="ec-th-actions">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $u)
                            <tr>
                                <td><div class="ec-row-title">{{ $u->name }}</div></td>
                                <td class="ec-muted">{{ $u->email }}</td>
                                <td>
                                    @if($u->is_active)
                                        <span class="ec-badge ec-badge-success">Sí</span>
                                    @else
                                        <span class="ec-badge ec-badge-muted">No</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="ec-actions">
                                        <a class="ec-icon-btn" href="{{ route('users.edit', $u->id) }}" title="Editar">
                                            <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true">
                                                <path fill="currentColor" d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zm2.92 2.33H5v-.92l9.06-9.06.92.92L5.92 19.58zM20.71 7.04a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                                            </svg>
                                        </a>

                                        <form method="POST" action="{{ route('users.destroy', $u->id) }}" class="ec-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="ec-icon-btn ec-icon-btn-danger js-delete-user"
                                                    data-name="{{ $u->name }}" title="Eliminar">
                                                <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path fill="currentColor" d="M6 7h12l-1 14H7L6 7zm3-3h6l1 2H8l1-2z"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="ec-empty">Aún no hay usuarios en este rol.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Clientes --}}
        <div class="ec-card ec-card-pad ec-section-card" style="margin-top: 14px;">
            <h2 class="ec-section-title">Usuarios tipo Cliente</h2>

            <div class="ec-table-wrap">
                <table class="ec-table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Cliente asociado</th>
                            <th>Activo</th>
                            <th class="ec-th-actions">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($clientUsers as $u)
                            <tr>
                                <td><div class="ec-row-title">{{ $u->name }}</div></td>
                                <td class="ec-muted">{{ $u->email }}</td>
                                <td class="ec-muted">
                                    {{ optional($u->client)->name ?? '—' }}
                                </td>
                                <td>
                                    @if($u->is_active)
                                        <span class="ec-badge ec-badge-success">Sí</span>
                                    @else
                                        <span class="ec-badge ec-badge-muted">No</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="ec-actions">
                                        <a class="ec-icon-btn" href="{{ route('users.edit', $u->id) }}" title="Editar">
                                            <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true">
                                                <path fill="currentColor" d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zm2.92 2.33H5v-.92l9.06-9.06.92.92L5.92 19.58zM20.71 7.04a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                                            </svg>
                                        </a>

                                        <form method="POST" action="{{ route('users.destroy', $u->id) }}" class="ec-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="ec-icon-btn ec-icon-btn-danger js-delete-user"
                                                    data-name="{{ $u->name }}" title="Eliminar">
                                                <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path fill="currentColor" d="M6 7h12l-1 14H7L6 7zm3-3h6l1 2H8l1-2z"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="ec-empty">Aún no hay usuarios en este rol.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.js-delete-user').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const form = btn.closest('form');
            const name = btn.dataset.name || 'este usuario';
            if (!window.Swal) {
                if (confirm('¿Eliminar ' + name + '?')) form.submit();
                return;
            }
            Swal.fire({
                icon: 'warning',
                title: '¿Eliminar usuario?',
                text: 'Se eliminará "' + name + '" y no se puede deshacer.',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#d33'
            }).then((result) => {
                if (result.isConfirmed) form.submit();
            });
        });
    });
});
</script>
@endsection
