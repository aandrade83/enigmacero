@extends('layouts.enigmacero')

@section('content')
<div class="login-wrapper">
  <div class="login-card">
    <h2>Nueva contraseña</h2>
    <p style="margin-top:6px; opacity:.85;">Escribe tu nueva contraseña.</p>

    <form method="POST" action="{{ route('password.update') }}" style="margin-top:18px;">
      @csrf
      <input type="hidden" name="token" value="{{ $token }}">

      <label>Correo electrónico</label>
      <input type="email" name="email" required class="input" value="{{ $email }}" readonly>

      <label style="margin-top:10px;">Nueva contraseña</label>
      <input type="password" name="password" required class="input">

      <label style="margin-top:10px;">Confirmar contraseña</label>
      <input type="password" name="password_confirmation" required class="input">

      <div style="margin-top:14px;">
        <button type="submit" class="btn-primary">Cambiar contraseña</button>
      </div>
    </form>
  </div>
</div>
@endsection
