@extends('layouts.enigmacero')

@section('title', 'EnigmaCero - Nuevo Cliente')

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
                <h1 class="ec-page-title">Nuevo cliente</h1>
            </div>

            <div class="ec-toolbar">
                <a href="{{ route('clients.index') }}" class="enigmacero-btn-secondary">Volver</a>
            </div>
        </div>

        <div class="ec-card ec-card-pad ec-form-card">
            <form method="POST" action="{{ route('clients.store') }}" class="ec-form">
                @csrf

                <div class="ec-field">
                    <label class="ec-label" for="name">Nombre</label>
                    <input id="name" class="enigmacero-input" type="text" name="name" value="{{ old('name') }}" required>
                    @error('name') <div class="ec-error">{{ $message }}</div> @enderror
                </div>

                <div class="ec-field">
                    <label class="ec-label" for="internal_email">Correo interno</label>
                    <input id="internal_email" class="enigmacero-input" type="email" name="internal_email" value="{{ old('internal_email') }}">
                    @error('internal_email') <div class="ec-error">{{ $message }}</div> @enderror
                </div>

                <div class="ec-field ec-field-inline">
                    <label class="ec-checkbox">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
                        <span>Activo</span>
                    </label>
                </div>

                @error('store') <div class="ec-error ec-error-box">{{ $message }}</div> @enderror

                <div class="ec-form-actions">
                    <button class="enigmacero-btn-primary" type="submit">Guardar</button>
                    <a class="enigmacero-btn-secondary" href="{{ route('clients.index') }}">Cancelar</a>
                </div>
            </form>
        </div>
    </section>
</div>
@endsection
