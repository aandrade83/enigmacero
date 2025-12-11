{{-- resources/views/auth/login.blade.php --}}
@extends('layouts.enigmacero')

@section('title', 'Ingresar - EnigmaCero')

@section('top-right')
    {{-- Aquí ponemos un botón parecido al de "Solicitar Demostración" del sitio --}}
    <a href="https://enigmacero.com" target="_blank" class="enigmacero-btn-primary">
        Solicitar demostración
    </a>
@endsection

@section('content')
    <div class="enigmacero-card">
        <h1 style="margin-top:0; margin-bottom:1.5rem; font-size:1.5rem;">
            Ingresar a EnigmaCero
        </h1>

        {{-- Mensajes de error --}}
        @if ($errors->any())
            <div style="margin-bottom:1rem; color:#b00020; font-size:0.9rem;">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf

            <label for="email" style="font-size:0.9rem;">Correo electrónico</label>
            <input id="email"
                   type="email"
                   name="email"
                   value="{{ old('email') }}"
                   class="enigmacero-input"
                   required
                   autofocus>

            <label for="password" style="font-size:0.9rem;">Contraseña</label>
            <input id="password"
                   type="password"
                   name="password"
                   class="enigmacero-input"
                   required>

            <button type="submit" class="enigmacero-btn-primary" style="margin-top:0.5rem;">
                Entrar
            </button>
        </form>
    </div>
@endsection
