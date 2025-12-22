@extends('layouts.enigmacero')

@section('content')
<div class="ec-dashboard-layout">
    @include('partials.sidebar')

    <main class="ec-main">
        <div class="ec-content-header">
            <h1>Agregar Usuario</h1>
        </div>

        @if($errors->any())
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    Swal.fire({ icon:'error', title:'Error', html:`{!! implode('<br>', $errors->all()) !!}` });
                });
            </script>
        @endif

        <div class="ec-card" style="padding:18px; max-width:820px;">
            <form method="POST" action="{{ route('users.store') }}" id="userForm">
                @csrf

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div>
                        <label style="font-weight:600; font-size:14px;">Nombre</label>
                        <input class="enigmacero-input" name="name" value="{{ old('name') }}" required>
                    </div>

                    <div>
                        <label style="font-weight:600; font-size:14px;">Email</label>
                        <input class="enigmacero-input" name="email" type="email" value="{{ old('email') }}" required>
                    </div>

                    <div>
                        <label style="font-weight:600; font-size:14px;">Rol</label>
                        <select class="enigmacero-input" name="role" id="roleSelect" required>
                            <option value="employee" {{ old('role')==='employee'?'selected':'' }}>Empleado</option>
                            <option value="admin" {{ old('role')==='admin'?'selected':'' }}>Administrador</option>
                            <option value="client" {{ old('role')==='client'?'selected':'' }}>Cliente</option>
                        </select>
                    </div>

                    <div id="clientBlock" style="display:none;">
                        <label style="font-weight:600; font-size:14px;">Cliente</label>
                        <select class="enigmacero-input" name="client_id" id="clientSelect">
                            <option value="">-- Seleccionar --</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}" {{ old('client_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                        <small id="clientHint" style="color:#6b7280;"></small>
                    </div>

                    <div>
                        <label style="font-weight:600; font-size:14px;">Contraseña</label>
                        <input class="enigmacero-input" name="password" type="password" required>
                    </div>

                    <div>
                        <label style="font-weight:600; font-size:14px;">Activo</label>
                        <select class="enigmacero-input" name="is_active">
                            <option value="1" {{ old('is_active','1')=='1'?'selected':'' }}>Sí</option>
                            <option value="0" {{ old('is_active')=='0'?'selected':'' }}>No</option>
                        </select>
                    </div>
                </div>

                <div style="margin-top:14px; display:flex; gap:10px;">
                    <button class="enigmacero-btn-primary" type="submit" id="btnSave">Guardar</button>
                    <a class="enigmacero-btn-secondary" href="{{ route('users.index') }}">Cancelar</a>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const roleSelect = document.getElementById('roleSelect');
    const clientBlock = document.getElementById('clientBlock');
    const clientSelect = document.getElementById('clientSelect');
    const clientHint = document.getElementById('clientHint');
    const btnSave = document.getElementById('btnSave');

    const clientsCount = {{ $clients->count() }};

    function sync() {
        const role = roleSelect.value;

        if (role === 'client') {
            clientBlock.style.display = 'block';

            if (clientsCount === 0) {
                clientHint.textContent = 'Necesitas crear primero un cliente.';
                btnSave.disabled = true;
            } else {
                clientHint.textContent = 'Seleccione el cliente asociado.';
                btnSave.disabled = false;
            }
        } else {
            clientBlock.style.display = 'none';
            clientSelect.value = '';
            clientHint.textContent = '';
            btnSave.disabled = false;
        }
    }

    roleSelect.addEventListener('change', sync);
    sync();
});
</script>
@endsection
