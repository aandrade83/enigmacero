@extends('layouts.enigmacero')

@section('title', 'EnigmaCero - Panel')

@section('top-right')
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="enigmacero-btn-secondary">Cerrar sesión</button>
    </form>
@endsection

@section('content')
@php
    // compatibilidad: si hoy lo pasás por Session o por Auth, no se rompe
    $displayName = $userName ?? (Auth::user()->name ?? 'Usuario');
@endphp

<div class="ec-dashboard">
    @include('partials.sidebar')

    <section class="ec-main">
        <div class="ec-welcome">
            Bienvenido, <strong>{{ $displayName }}</strong>.
        </div>

        <div class="ec-quote-card">
            <div class="ec-quote-text">
                <strong>{{ $quote ?? 'La inteligencia de negocios comienza con buenas preguntas.' }}</strong>
            </div>
            <div class="ec-quote-author">— {{ $quoteAuthor ?? 'EnigmaCero' }}</div>
        </div>
    </section>
</div>
@endsection
