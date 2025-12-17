@extends('layouts.enigmacero')

@section('title', 'Administración de Clientes - EnigmaCero')

@section('content')
<div class="ec-dashboard-layout">
    @include('partials.sidebar')

    <main class="ec-content">
        <div class="ec-content-header">
            <h1>Administración de Clientes</h1>

            <a href="{{ route('clients.create') }}" class="btn btn-success">
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
                        <th style="width:110px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clients as $c)
                        <tr>
                            <td>{{ $c->id }}</td>
                            <td>{{ $c->name }}</td>
                            <td>{{ $c->folder }}</td>
                            <td>{{ $c->is_active ? 'Sí' : 'No' }}</td>
                            <td>{{ optional($c->created_at)->format('Y-m-d H:i') }}</td>
                            <td>
                                <a href="#" title="Editar">✏️</a>
                                &nbsp;
                                <a href="#" title="Eliminar">🗑️</a>
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
