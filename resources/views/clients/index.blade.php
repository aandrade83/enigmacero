@extends('layouts.app')

@section('title', 'Administración de Clientes - EnigmaCero')

@section('content')
<div class="ec-layout">
    @include('partials.sidebar')

    <main class="ec-main">
        <div class="ec-content-header">
            <h1>Administración de Clientes</h1>

            <a href="{{ route('clients.create') }}" class="enigmacero-btn-primary">+ Nuevo Cliente</a>
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
                                <a href="{{ route('clients.edit', $c) }}" title="Editar">✏️</a>

                                <form action="{{ route('clients.destroy', $c) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="border:none;background:none;cursor:pointer;" title="Eliminar">🗑️</button>
                                </form>
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
