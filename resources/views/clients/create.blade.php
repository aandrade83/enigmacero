@extends('layouts.app')

@section('content')
<div class="ec-layout">
    @include('partials.sidebar')

    <main class="ec-main">
        <h1>Nuevo Cliente</h1>

        <form method="POST" action="{{ route('clients.store') }}" class="ec-card" style="margin-top:16px;padding:16px;">
            @csrf

            <div style="margin-bottom:12px;">
                <label>Nombre</label>
                <input type="text" name="name" value="{{ old('name') }}" required>
                @error('name') <div style="color:#c00;">{{ $message }}</div> @enderror
            </div>

            <div style="margin-bottom:12px;">
                <label>Correo interno</label>
                <input type="email" name="internal_email" value="{{ old('internal_email') }}">
                @error('internal_email') <div style="color:#c00;">{{ $message }}</div> @enderror
            </div>

            <div style="margin-bottom:12px;">
                <label>
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                    Activo
                </label>
            </div>

            <div style="display:flex;gap:10px;">
                <button class="ec-btn ec-btn-primary" type="submit">Guardar</button>
                <a class="ec-btn" href="{{ route('clients.index') }}">Cancelar</a>
            </div>
        </form>
    </main>
</div>
@endsection

