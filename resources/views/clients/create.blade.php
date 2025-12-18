@extends('layouts.enigmacero')

@section('content')
<div class="ec-dashboard-layout">
    @include('partials.sidebar')

    <section class="ec-content">
        <div class="ec-card" style="padding:16px;">
            <h1 style="margin:0 0 12px 0;">Nuevo Cliente</h1>

            <form method="POST" action="{{ route('clients.store') }}">
                @csrf

                <div style="margin-bottom:12px;">
                    <label>Nombre</label><br>
                    <input type="text" name="name" value="{{ old('name') }}" required>
                    @error('name') <div style="color:#c00;">{{ $message }}</div> @enderror
                </div>

                <div style="margin-bottom:12px;">
                    <label>Correo interno</label><br>
                    <input type="email" name="internal_email" value="{{ old('internal_email') }}">
                    @error('internal_email') <div style="color:#c00;">{{ $message }}</div> @enderror
                </div>

                <div style="margin-bottom:12px;">
                    <label>
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
                        Activo
                    </label>
                </div>

                <div style="display:flex;gap:10px;">
                    <button class="ec-btn ec-btn-primary" type="submit">Guardar</button>
                    <a class="ec-btn" href="{{ route('clients.index') }}">Cancelar</a>
                </div>
            </form>
        </div>
    </section>
</div>
@endsection
