@extends('layouts.enigmacero')

@section('title', 'EnigmaCero - Editar Cliente')

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
                <h1 class="ec-page-title">Editar cliente</h1>
            </div>

            <div class="ec-toolbar">
                <a href="{{ route('clients.index') }}" class="enigmacero-btn-secondary">Volver</a>
            </div>
        </div>

        <div class="ec-card ec-card-pad ec-form-card">
            <form method="POST" action="{{ route('clients.update', $client) }}" class="ec-form">
                @csrf
                @method('PUT')

                <div class="ec-field">
                    <label class="ec-label" for="name">Nombre</label>
                    <input id="name" class="enigmacero-input" type="text" name="name" value="{{ old('name', $client->name) }}" required>
                    @error('name') <div class="ec-error">{{ $message }}</div> @enderror
                </div>

                <div class="ec-field">
                    <label class="ec-label" for="internal_email">Correo interno</label>
                    <input id="internal_email" class="enigmacero-input" type="email" name="internal_email" value="{{ old('internal_email', $client->internal_email) }}">
                    @error('internal_email') <div class="ec-error">{{ $message }}</div> @enderror
                </div>

                <div class="ec-field ec-field-inline">
                    <label class="ec-checkbox">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $client->is_active) ? 'checked' : '' }}>
                        <span>Activo</span>
                    </label>
                </div>

                <div class="ec-form-actions">
                    <button class="enigmacero-btn-primary" type="submit">Actualizar</button>
                    <a class="enigmacero-btn-secondary" href="{{ route('clients.index') }}">Cancelar</a>
                </div>
            </form>
        </div>
    </section>
</div>
@endsection
