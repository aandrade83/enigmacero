@extends('layouts.enigmacero')

@section('title', 'EnigmaCero - Agregar usuario')

@section('top-right')
<form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit" class="enigmacero-btn-ghost">Cerrar sesión</button>
</form>
@endsection

@section('content')
<div class="ec-dashboard">
    @include('partials.sidebar')

    <section class="ec-main">
        <div class="ec-content-header">
            <div>
                <div class="ec-page-kicker">Administración</div>
                <h1 class="ec-page-title">Agregar Usuario</h1>
            </div>

            <div class="ec-toolbar">
                <a href="{{ route('users.index') }}" class="ec-btn ec-btn-secondary">Volver</a>
            </div>
        </div>

        <div class="ec-card ec-card-pad">
            <form method="POST" action="{{ route('users.store') }}" class="ec-form" id="userForm">
                @csrf

                <div class="ec-form-row">
                    <div class="ec-field">
                        <label class="ec-label" for="name">Nombre</label>
                        <input class="ec-input" id="name" name="name" value="{{ old('name') }}" required>
                    </div>

                    <div class="ec-field">
                        <label class="ec-label" for="email">Email</label>
                        <input class="ec-input" id="email" name="email" type="email" value="{{ old('email') }}" required>
                    </div>
                </div>

                <div class="ec-form-row">
                    <div class="ec-field">
                        <label class="ec-label" for="role">Rol</label>
                        <select class="ec-select" id="role" name="role" required>
                            <option value="admin" {{ old('role')==='admin' ? 'selected' : '' }}>Administrador</option>
                            <option value="employee" {{ old('role','employee')==='employee' ? 'selected' : '' }}>Empleado</option>
                            <option value="client" {{ old('role')==='client' ? 'selected' : '' }}>Cliente</option>
                        </select>
                        <div class="ec-help">Si eliges “Cliente”, debes asociarlo a un cliente existente.</div>
                    </div>

                    <div class="ec-field">
                        <label class="ec-label" for="password">Contraseña</label>
                        <input class="ec-input" id="password" name="password" type="password" required>
                    </div>
                </div>

                <div class="ec-form-row" id="clientRow" style="display:none;">
                    <div class="ec-field" style="grid-column: 1 / -1;">
                        <label class="ec-label" for="client_id">Cliente asociado</label>
                        <select class="ec-select" id="client_id" name="client_id">
                            <option value="">Seleccione un cliente…</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}" {{ old('client_id')==$c->id ? 'selected' : '' }}>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="ec-help" id="noClientsMsg" style="display:none;">
                            No hay clientes creados. Debes crear un cliente antes de crear un usuario “Cliente”.
                        </div>
                    </div>
                </div>

                <div class="ec-form-row">
                    <div class="ec-field">
                        <label class="ec-label" for="is_active">Activo</label>
                        <select class="ec-select" id="is_active" name="is_active">
                            <option value="1" {{ old('is_active', '1')==='1' ? 'selected' : '' }}>Sí</option>
                            <option value="0" {{ old('is_active')==='0' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                </div>

                <div class="ec-form-actions">
                    <button class="ec-btn ec-btn-primary" type="submit" id="saveBtn">Guardar</button>
                    <a href="{{ route('users.index') }}" class="ec-btn ec-btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const role = document.getElementById('role');
    const clientRow = document.getElementById('clientRow');
    const clientSelect = document.getElementById('client_id');
    const noClientsMsg = document.getElementById('noClientsMsg');
    const saveBtn = document.getElementById('saveBtn');
    const hasClients = {{ (isset($clients) && count($clients) > 0) ? 'true' : 'false' }};

    function refreshClientUI() {
        const isClient = role.value === 'client';
        clientRow.style.display = isClient ? '' : 'none';

        if (!isClient) {
            clientSelect.required = false;
            noClientsMsg.style.display = 'none';
            saveBtn.disabled = false;
            return;
        }

        if (!hasClients) {
            noClientsMsg.style.display = '';
            clientSelect.required = false;
            saveBtn.disabled = true;
        } else {
            noClientsMsg.style.display = 'none';
            clientSelect.required = true;
            saveBtn.disabled = false;
        }
    }

    role.addEventListener('change', refreshClientUI);
    refreshClientUI();

    @if (session('success'))
    if (window.Swal) {
        Swal.fire({ icon: 'success', title: 'Listo', text: @json(session('success')) });
    }
    @endif

    @if ($errors->any())
    if (window.Swal) {
        Swal.fire({
            icon: 'error',
            title: 'Revisa los campos',
            html: @json('<ul style="text-align:left; margin:0; padding-left:1.25rem;">' . implode('', $errors->all('<li>:message</li>')) . '</ul>')
        });
    }
    @endif
});
</script>
@endsection
