{{-- resources/views/dashboard.blade.php --}}
@extends('layouts.enigmacero')

@section('title', 'Panel principal - EnigmaCero')

@section('content')
    <div class="enigmacero-card">
        <h1 style="margin-top:0; margin-bottom:1rem; font-size:1.6rem;">
            Panel principal
        </h1>

        <p style="margin-bottom:1.5rem;">
            Bienvenido,
            <strong>{{ $user['name'] ?? 'Usuario' }}</strong>.
        </p>

        <p style="margin-bottom:2rem;">
            Aquí vamos a ir mostrando los módulos de EnigmaCero (análisis, reportes,
            etc.) conforme los vayamos construyendo.
        </p>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="enigmacero-btn-primary">
                Cerrar sesión
            </button>
        </form>
    </div>
@endsection
