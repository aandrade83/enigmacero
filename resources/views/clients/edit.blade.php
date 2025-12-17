@extends('layouts.app')

@section('content')
<div class="ec-layout">
    @include('partials.sidebar')

    <main class="ec-main">
        <h1>Editar Cliente</h1>

        <form method="POST" action="{{ route('clients.update', $client) }}" class="ec-card" style="margin-top:16px;padding:16px;">
            @csrf
            @method('PUT')

            <div style="margin-bottom:12px;">
                <label>Nombre</label>
                <input type="text" name="name" value="{{ old('name', $client->name) }}" required>
                @error('name') <div style="color:#c00;">{{ $message }}</div> @enderror
            </div>

            <div style="margin-bottom:12px;">
                <label>Correo interno</label>
                <input type="email" name="internal_email" value="{{ old('internal_email', $client->internal_email) }}">
                @error('internal_email') <div style="color:#c00;">{{ $message }}</div> @enderror
            </div>

            <div style="margin-bottom:12px;">
                <label>Carpeta (solo lectura)</label>
                <input type="text" value="{{ $client->folder }}" readonly>
            </div>

            <div style="margin-bottom:12px;">
                <label>
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $client->is_active) ? 'checked' : '' }}>
                    Activo
                </label>
            </div>

            <div style="display:flex;gap:10px;">
                <button class="ec-btn ec-btn-primary" type="submit">Actualizar</button>
                <a class="ec-btn" href="{{ route('clients.index') }}">Volver</a>
            </div>
        </form>
    </main>
</div>
@endsection
