@extends('layouts.enigmacero')

@section('content')
<div class="ec-dashboard-layout">
    @include('partials.sidebar')

    <main class="ec-main">
        <div class="ec-content-header">
            <h1>Administración de Clientes</h1>

            {{-- IMPORTANTE: cerrar el <a> --}}
            <a href="{{ route('clients.create') }}" class="ec-btn ec-btn-primary">
                + Nuevo Cliente
            </a>
        </div>

        <div class="ec-card">
            <table class="ec-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Carpeta</th>
                        <th>Activo</th>
                        <th>Creado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($clients as $c)
                    <tr>
                        <td>{{ $c->id }}</td>
                        <td>{{ $c->name }}</td>
                        <td>{{ $c->bucket_folder ?? '-' }}</td>
                        <td>{{ $c->is_active ? 'Sí' : 'No' }}</td>
                        <td>{{ $c->created_at }}</td>
                        <td>
                            <a href="{{ route('clients.edit', $c) }}">Editar</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">No hay clientes todavía.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </main>
</div>
@endsection
