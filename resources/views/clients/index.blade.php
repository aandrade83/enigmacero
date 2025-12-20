@extends('layouts.enigmacero')

@section('title', 'EnigmaCero - Clientes')

@section('top-right')
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="enigmacero-btn-secondary">Cerrar sesión</button>
    </form>
@endsection

@section('content')
<div class="ec-dashboard">
    @include('partials.sidebar')

    <section class="ec-main">
        <div class="ec-content-header">
            <div>
                <div class="ec-page-kicker">Administración</div>
                <h1 class="ec-page-title">Clientes</h1>
            </div>

            <div class="ec-toolbar">
                <a href="{{ route('clients.create') }}" class="enigmacero-btn-primary ec-btn-with-icon">
                    <span class="ec-btn-icon" aria-hidden="true">+</span>
                    Nuevo cliente
                </a>
            </div>
        </div>

        <div class="ec-card ec-card-pad">
            <table class="ec-table">
                <thead>
                    <tr>
                        <th style="width:72px;">ID</th>
                        <th>Nombre</th>
                        <th style="width:180px;">Carpeta</th>
                        <th style="width:110px;">Activo</th>
                        <th style="width:200px;">Creado</th>
                        <th style="width:140px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($clients as $c)
                    <tr>
                        <td class="ec-mono">{{ $c->id }}</td>
                        <td>
                            <div class="ec-row-title">{{ $c->name }}</div>
                            @if($c->internal_email)
                                <div class="ec-row-sub">{{ $c->internal_email }}</div>
                            @endif
                        </td>
                        <td class="ec-mono">{{ $c->bucket_folder ?? '-' }}</td>
                        <td>
                            @if($c->is_active)
                                <span class="ec-badge ec-badge-success">Sí</span>
                            @else
                                <span class="ec-badge">No</span>
                            @endif
                        </td>
                        <td class="ec-muted">{{ $c->created_at }}</td>
                        <td>
                            <div class="ec-actions">
                                <a class="ec-icon-btn" href="{{ route('clients.edit', $c) }}" title="Editar">
                                    {{-- pencil --}}
                                    <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true">
                                        <path fill="currentColor" d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zm2.92 2.83H5v-.92l9.06-9.06.92.92-9.06 9.06zM20.71 7.04a1.003 1.003 0 0 0 0-1.42l-2.34-2.34a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 1.84-1.82z"/>
                                    </svg>
                                </a>

                                <form method="POST" action="{{ route('clients.destroy', $c) }}" class="ec-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                            class="ec-icon-btn ec-icon-btn-danger js-delete-client"
                                            data-client-name="{{ $c->name }}"
                                            title="Eliminar">
                                        {{-- trash --}}
                                        <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true">
                                            <path fill="currentColor" d="M6 7h12l-1 14H7L6 7zm3-3h6l1 2H8l1-2z"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="ec-empty">
                            No hay clientes todavía.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection

@section('page-scripts')
<script>
    window.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.js-delete-client').forEach((btn) => {
            btn.addEventListener('click', () => {
                const form = btn.closest('form');
                const name = btn.getAttribute('data-client-name') || 'este cliente';

                if (!window.Swal) {
                    form.submit();
                    return;
                }

                Swal.fire({
                    icon: 'warning',
                    title: '¿Eliminar cliente?',
                    text: `Vas a eliminar ${name}. Esta acción no se puede deshacer.`,
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#d33',
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    });
</script>
@endsection
