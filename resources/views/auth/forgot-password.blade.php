@extends('layouts.enigmacero')

@section('content')
<div class="login-wrapper">
  <div class="login-card">
    <h2>Recuperar contraseña</h2>
    <p style="margin-top:6px; opacity:.85;">Ingresa tu correo y te enviaremos un enlace para restablecer tu contraseña.</p>

    <form method="POST" action="{{ route('password.email') }}" style="margin-top:18px;">
      @csrf

      <label>Correo electrónico</label>
      <input type="email" name="email" required class="input" value="{{ old('email') }}">

      <div style="display:flex; justify-content:space-between; align-items:center; margin-top:14px;">
        <button type="submit" class="btn-primary">Enviar</button>

        <a href="{{ route('login') }}" style="font-size:13px; color:#7bbf2a; text-decoration:none;">
          Volver al login
        </a>
      </div>
    </form>
  </div>
</div>
@endsection
