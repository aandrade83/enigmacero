@extends('layouts.enigmacero')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="ec-dashboard-layout">
    @include('partials.sidebar')

    <main class="ec-main">
        <div class="ec-content-header" style="display:flex; justify-content:space-between; align-items:center;">
            <h1>Usuarios</h1>
            <a class="enigmacero-btn-primary" href="{{ route('users.create') }}">+ Agregar usuario</a>
        </div>

        @if(session('success'))
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    Swal.fire({ icon:'success', title:'Listo', text:@json(session('success')) });
                });
            </script>
        @endif

        @if($errors->any())
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    Swal.fire({ icon:'error', title:'Error', html:`{!! implode('<br>', $errors->all()) !!}` });
                });
            </script>
        @endif

        {{-- ADMIN --}}
        <div class="ec-card" style="margin-top:14px; padding:18px;">
            <h2 style="margin:0 0 12px 0;">Administradores</h2>

            @if($admins->isEmpty())
                <div style="color:#6b7280;">Aún no hay usuarios en este rol.</div>
            @else
                <table class="ec-table" style="width:100%;">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Activo</th>
                            <th style="width:140px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($admins as $u)
                            <tr>
                                <td style="font-weight:700;">{{ $u->name }}</td>
                                <td>{{ $u->email }}</td>
                                <td>{{ $u->is_active ? 'Sí' : 'No' }}</td>
                                <td>
                                    <div style="display:flex; gap:10px; align-items:center;">
                                        <a title="Editar" href="{{ route('users.edit', $u) }}">✏️</a>

                                        <form method="POST" action="{{ route('users.destroy', $u) }}" class="js-delete-user">
                                            @csrf @method('DELETE')
                                            <button type="submit" style="border:none; background:transparent; cursor:pointer;" title="Eliminar">🗑️</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        {{-- EMPLOYEES --}}
        <div class="ec-card" style="margin-top:14px; padding:18px;">
            <h2 style="margin:0 0 12px 0;">Usuarios / Empleados</h2>

            @if($employees->isEmpty())
                <div style="color:#6b7280;">Aún no hay usuarios en este rol.</div>
            @else
                <table class="ec-table" style="width:100%;">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Activo</th>
                            <th style="width:140px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $u)
                            <tr>
                                <td style="font-weight:700;">{{ $u->name }}</td>
                                <td>{{ $u->email }}</td>
                                <td>{{ $u->is_active ? 'Sí' : 'No' }}</td>
                                <td>
                                    <div style="display:flex; gap:10px; align-items:center;">
                                        <a title="Editar" href="{{ route('users.edit', $u) }}">✏️</a>
                                        <form method="POST" action="{{ route('users.destroy', $u) }}" class="js-delete-user">
                                            @csrf @method('DELETE')
                                            <button type="submit" style="border:none; background:transparent; cursor:pointer;" title="Eliminar">🗑️</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        {{-- CLIENT USERS --}}
        <div class="ec-card" style="margin-top:14px; padding:18px;">
            <h2 style="margin:0 0 12px 0;">Usuarios tipo Cliente</h2>

            @if($clientUsers->isEmpty())
                <div style="color:#6b7280;">Aún no hay usuarios en este rol.</div>
            @else
                <table class="ec-table" style="width:100%;">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Cliente asociado</th>
                            <th>Activo</th>
                            <th style="width:140px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clientUsers as $u)
                            <tr>
                                <td style="font-weight:700;">{{ $u->name }}</td>
                                <td>{{ $u->email }}</td>
                                <td>{{ $u->client?->name ?? '(sin cliente)' }}</td>
                                <td>{{ $u->is_active ? 'Sí' : 'No' }}</td>
                                <td>
                                    <div style="display:flex; gap:10px; align-items:center;">
                                        <a title="Editar" href="{{ route('users.edit', $u) }}">✏️</a>
                                        <form method="POST" action="{{ route('users.destroy', $u) }}" class="js-delete-user">
                                            @csrf @method('DELETE')
                                            <button type="submit" style="border:none; background:transparent; cursor:pointer;" title="Eliminar">🗑️</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.js-delete-user').forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const ok = await Swal.fire({
                icon: 'warning',
                title: '¿Eliminar usuario?',
                text: 'Esta acción no se puede deshacer.',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            });
            if (ok.isConfirmed) form.submit();
        });
    });
});
</script>
@endsection
