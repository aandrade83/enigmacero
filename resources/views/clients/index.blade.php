@extends('layouts.app')

@section('content')
<div class="ec-layout">
    @include('partials.sidebar')

    <main class="ec-main">
        <div class="ec-main-header" style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
            <h1 style="margin:0;">Administración de Clientes</h1>
            <a class="ec-btn ec-btn-primary" href="{{ route('clients.create') }}">+ Nuevo Cliente</a>
        </div>

        <div class="ec-card" style="margin-top:16px;">
            @if(($clients ?? collect())->count() === 0)
                <p>No hay clientes todavía.</p>
            @else
                <table class="ec-table" style="width:100%;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Carpeta</th>
                            <th>Activo</th>
                            <th>Creado</th>
                            <th style="width:140px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clients as $client)
                            <tr>
                                <td>{{ $client->id }}</td>
                                <td>{{ $client->name }}</td>
                                <td>{{ $client->folder }}</td>
                                <td>{{ $client->is_active ? 'Sí' : 'No' }}</td>
                                <td>{{ optional($client->created_at)->format('Y-m-d H:i') }}</td>
                                <td>
                                    <a class="ec-btn ec-btn-sm" href="{{ route('clients.edit', $client) }}">✏️</a>

                                    <form id="delete-client-{{ $client->id }}" action="{{ route('clients.destroy', $client) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="ec-btn ec-btn-sm ec-btn-danger"
                                                type="submit"
                                                data-confirm-delete="delete-client-{{ $client->id }}">
                                            🗑️
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </main>
</div>

{{-- SweetAlert2 (CDN) --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('click', function (e) {
    const btn = e.target.closest('[data-confirm-delete]');
    if (!btn) return;

    e.preventDefault();
    const formId = btn.getAttribute('data-confirm-delete');
    const form = document.getElementById(formId);

    Swal.fire({
        title: '¿Eliminar cliente?',
        text: 'Esto borrará también su carpeta en el bucket.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed && form) form.submit();
    });
});

@if(session('success'))
Swal.fire({ icon:'success', title:'Listo', text: @json(session('success')) });
@endif

@if(session('error'))
Swal.fire({ icon:'error', title:'Error', text: @json(session('error')) });
@endif
</script>
@endsection
